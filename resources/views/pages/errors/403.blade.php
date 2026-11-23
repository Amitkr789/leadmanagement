@extends('errors.minimal', [
    'code'    => 403,
    'heading' => 'Access denied',
    'text'    => 'You do not have permission to open this page. Ask your company admin if you need access.',
])