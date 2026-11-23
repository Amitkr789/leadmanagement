<?php
namespace App\Console\Commands;

use App\Models\{CalendarAlert, CalendarEvent, CrmCalendar, PushToken};
use App\Services\Calendar\{CalendarAccess, Fcm, Reminders};
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Console\Command;

/**
 * Runs every minute (see routes/console.php). Finds reminders that are due, writes one alert row per person
 * (the unique key makes a second run harmless), and pushes it to every browser / phone that person enabled.
 */
class DispatchCalendarReminders extends Command
{
    protected $signature = 'calendar:dispatch';
    protected $description = 'Send due calendar reminders to browsers and phones';

    private array $audience = [];

    public function handle(Fcm $fcm): int
    {
        $fmt = 'Y-m-d H:i:s';
        $now = new DateTimeImmutable('now', new DateTimeZone(config('app.timezone')));
        $grace = (int) config('calendar.grace_minutes', 30);
        $horizon = $now->modify('+' . (40320 + 1440) . ' minutes')->format($fmt);   // longest reminder is 4 weeks
        $cut = $now->modify("-{$grace} minutes")->modify('-1 day')->format($fmt);
        $push = $fcm->configured();
        $made = 0; $sent = 0;

        CalendarEvent::query()->with('calendar')->whereNotNull('reminders')->where('start_at', '<=', $horizon)
            ->where(function ($q) use ($cut, $now) {
                $q->where(fn ($x) => $x->where('repeat', 'none')->where('end_at', '>=', $cut))
                  ->orWhere(fn ($x) => $x->where('repeat', '!=', 'none')
                      ->where(fn ($y) => $y->whereNull('repeat_until')->orWhere('repeat_until', '>=', $now->format('Y-m-d'))));
            })
            ->chunkById(200, function ($events) use ($fmt, $now, $grace, $push, $fcm, &$made, &$sent) {
                foreach ($events as $e) {
                    $mins = array_values($e->reminders ?? []);
                    if (!$mins || !$e->calendar) continue;
                    $due = Reminders::due($e->start_at, $e->end_at, (bool) $e->all_day, $e->repeat ?: 'none', $e->repeat_until, $e->exdates ?? [], $mins, $now, $grace);
                    if (!$due) continue;
                    $people = $this->recipients($e);

                    foreach ($due as [$os, $m]) {
                        $url = route('calendar.index', ['open' => $e->id, 'at' => $os->format('Y-m-d')]);
                        $body = $this->body($e, $os, $m, $now);
                        foreach ($people as $u) {
                            $key = ['event_id' => $e->id, 'user_id' => $u->id, 'occurrence_at' => $os->format($fmt), 'minutes_before' => $m];
                            $new = CalendarAlert::query()->insertOrIgnore([$key + [
                                'company_id' => $e->company_id, 'title' => $e->title, 'body' => $body, 'url' => $url, 'fired_at' => $now->format($fmt),
                            ]]);
                            if ($new !== 1) continue;            // already sent on an earlier run
                            $made++;
                            if (!$push) continue;

                            $id = CalendarAlert::where($key)->value('id');
                            $ok = 0;
                            foreach (PushToken::where('user_id', $u->id)->get() as $t) {
                                $res = $fcm->send($t, $e->title, $body, $url, ['alert_id' => $id, 'tag' => 'cal-' . $id]);
                                if ($res === 'ok') $ok++;
                                elseif ($res === 'invalid') $t->delete();       // browser was uninstalled / permission revoked
                            }
                            if ($ok) { $sent += $ok; CalendarAlert::whereKey($id)->update(['push_sent_at' => $now->format($fmt)]); }
                        }
                    }
                }
            });

        if ($made) $this->info("Reminders: {$made} new, {$sent} pushed" . ($push ? '' : ' (Firebase is not configured, in-app only)'));
        return self::SUCCESS;
    }

    /** "Only me" goes to the creator; "everyone" to everybody who can see the calendar. */
    private function recipients(CalendarEvent $e)
    {
        if ($e->notify !== 'everyone') return $e->created_by ? \App\Models\User::whereKey($e->created_by)->get() : collect();
        return $this->audience[$e->calendar_id] ??= CalendarAccess::audience($e->calendar)->get();
    }

    private function body(CalendarEvent $e, DateTimeImmutable $os, int $m, DateTimeImmutable $now): string
    {
        if ($e->all_day) {
            $days = (int) $now->setTime(0, 0)->diff($os->setTime(0, 0))->format('%r%a');
            $parts = [($days <= 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : 'On ' . $os->format('j M'))) . ' · All day'];
        } else {
            $parts = [($m === 0 ? 'Starting now' : 'In ' . Reminders::human($m)) . ' · ' . $os->format('g:i A')];
        }
        if ($e->location) $parts[] = $e->location;
        return mb_strimwidth(implode(' · ', $parts), 0, 290, '…');
    }
}
