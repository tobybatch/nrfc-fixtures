<?php

namespace App\DTO;

final class ImportExportDTO
{
    /**
     * @var array<string>
     */
    private array $errors = array();
    private int $successCount = 0;
    private int $updateCount = 0;

    public function getUpdateCount(): int
    {
        return $this->updateCount;
    }

    public function incrementUpdateCount(): int
    {
        $this->updateCount++;
        return $this->updateCount;
    }

    public function decrementUpdateCount(): int
    {
        $this->updateCount--;
        return $this->updateCount;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function removeError(string $error): void
    {
        unset($this->errors[$error]);
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function incrementSuccessCount(): int
    {
        $this->successCount++;
        return $this->successCount;
    }

    public function decrementSuccessCount(): int
    {
        $this->successCount--;
        return $this->successCount;
    }
}