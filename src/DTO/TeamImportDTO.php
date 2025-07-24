<?php

namespace App\DTO;

use App\Config\Team;

class TeamImportDTO
{
    private Team $team;
    private int $column;

    /**
     * @param Team $team
     * @param int $column
     */
    public function __construct(Team $team, int $column)
    {
        $this->team = $team;
        $this->column = $column;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function setColumn(int $column): TeamImportDTO
    {
        $this->column = $column;
        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): TeamImportDTO
    {
        $this->team = $team;
        return $this;
    }
}