<?php
class Event {
    public function __construct(
      public string $level,
      public string $message,
      public array  $context,
      public \DateTimeImmutable $timestamp,
      public ?string $errorId = null,
      public ?string $file = null,
      public ?int    $line = null,
      // …etc…
    ) {}
  }
  