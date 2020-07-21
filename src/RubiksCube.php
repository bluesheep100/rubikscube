<?php

namespace Rubik;

use Tightenco\Collect\Support\Collection;

class RubiksCube
{
    protected $state;

    // RED,   GREEN, BLUE,  YELLOW, WHITE, ORANGE
    // Front, Left,  Right, Bottom, Top,   Back
    const RED = 'r';
    const GREEN = 'g';
    const BLUE = 'b';
    const YELLOW = 'y';
    const WHITE = 'w';
    const ORANGE = 'o';

    const RED_FACE = [
        ['r', 'r', 'r'],
        ['r', 'r', 'r'],
        ['r', 'r', 'r'],
    ];
    const GREEN_FACE = [
        ['g', 'g', 'g'],
        ['g', 'g', 'g'],
        ['g', 'g', 'g'],
    ];
    const BLUE_FACE = [
        ['b', 'b', 'b'],
        ['b', 'b', 'b'],
        ['b', 'b', 'b'],
    ];
    const YELLOW_FACE = [
        ['y', 'y', 'y'],
        ['y', 'y', 'y'],
        ['y', 'y', 'y'],
    ];
    const WHITE_FACE = [
        ['w', 'w', 'w'],
        ['w', 'w', 'w'],
        ['w', 'w', 'w'],
    ];
    const ORANGE_FACE = [
        ['o', 'o', 'o'],
        ['o', 'o', 'o'],
        ['o', 'o', 'o'],
    ];

    public function __construct()
    {
        $this->state = $this->r_collect([
            self::RED => self::RED_FACE,
            self::GREEN => self::GREEN_FACE,
            self::BLUE => self::BLUE_FACE,
            self::YELLOW => self::YELLOW_FACE,
            self::WHITE => self::WHITE_FACE,
            self::ORANGE => self::ORANGE_FACE,
        ]);
    }

    /**
     * Recursive variant of the Laravel collect() helper.
     *
     * @param $array
     * @return Collection
     */
    protected function r_collect($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->r_collect($value);
                $array[$key] = $value;
            }
        }

        return collect($array);
    }

    /**
     * Returns a copy of a column at $index on $face
     * @param $face
     * @param $index
     * @return Collection
     */
    protected function getCol($face, $index)
    {
        return $this->state->get($face)->map(function (Collection $item) use ($index) {
            return $item[$index];
        });
    }

    /**
     * Sets a given column on the specified face and index.
     *
     * @param $column
     * @param $face
     * @param $index
     * @return Collection
     */
    protected function setCol($column, $face, $index)
    {
        $oldCol = $this->getCol($face, $index);

        foreach ($this->state[$face] as $i => $row) {
            $row[$index] = $column[$i];
        }

        return $oldCol;
    }

    /**
     * Sets a given row on the specified face and index.
     *
     * @param $row
     * @param $face
     * @param $index
     * @return Collection
     */
    protected function setRow($row, $face, $index)
    {
        $oldRow = $this->state[$face][$index];

        $this->state[$face][$index] = $row;

        return $oldRow;
    }

    /**
     * Turns a given face 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param $face
     * @param bool $invert
     */
    protected function turnFace($face, $invert = false)
    {
        $left = $this->getCol($face, 0);
        $right = $this->getCol($face, 2);
        $top_mid = $this->state[$face][0][1];
        $bot_mid = $this->state[$face][2][1];

        if (!$invert) {
            $left = $left->reverse();
            $right = $right->reverse();

            $this->setRow($left, $face, 0);
            $this->setRow($right, $face, 2);

            $this->state[$face][1][2] = $top_mid;
            $this->state[$face][1][0] = $bot_mid;
        } else {
            $this->setRow($right, $face, 0);
            $this->setRow($left, $face, 2);

            $this->state[$face][1][0] = $top_mid;
            $this->state[$face][1][2] = $bot_mid;
        }
    }

    /**
     * Rotates the left side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param array $params
     * @return RubiksCube
     */
    protected function doTurn($params)
    {
        $params['rows'] = $params['rows'] ?? false;

        list(
            'faces' => $faces,
            'exclude' => $exclude,
            'invert' => $invert,
            'index' => $index,
            'rows' => $rows
            ) = $params;

        if ($invert) {
            $faces = array_reverse($faces);
        }

        if ($rows) {
            $row = $this->state[$faces[0]][$index];
            $row = $this->setRow($row, $faces[1], $index);
            $row = $this->setRow($row, $faces[2], $index);
            $row = $this->setRow($row, $faces[3], $index);
            $this->setRow($row, $faces[0], $index);
        } else {
            $col = $this->getCol($faces[0], $index);
            $col = $this->setCol($col, $faces[1], $index);
            $col = $this->setCol($col, $faces[2], $index);
            $col = $this->setCol($col, $faces[3], $index);
            $this->setCol($col, $faces[0], $index);
        }

        $this->turnFace($exclude, $invert);

        return $this;
    }

    /**
     * Rotates the left side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function left($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::RED, self::YELLOW, self::ORANGE, self::WHITE],
            'exclude' => self::GREEN,
            'invert' => $invert,
            'index' => 0,
        ]);
    }

    /**
     * Rotates the right side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function right($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::RED, self::WHITE, self::ORANGE, self::YELLOW],
            'exclude' => self::BLUE,
            'invert' => $invert,
            'index' => 2,
        ]);
    }

    /**
     * Rotates the top side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function top($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::RED, self::GREEN, self::ORANGE, self::BLUE],
            'exclude' => self::WHITE,
            'invert' => $invert,
            'rows' => true,
            'index' => 0,
        ]);
    }

    /**
     * Rotates the bottom side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function bottom($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::RED, self::BLUE, self::ORANGE, self::GREEN],
            'exclude' => self::YELLOW,
            'invert' => $invert,
            'rows' => true,
            'index' => 2,
        ]);
    }

    /**
     * Rotates the front side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function front($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::WHITE, self::BLUE, self::YELLOW, self::GREEN],
            'exclude' => self::RED,
            'invert' => $invert,
            'rows' => true,
            'index' => 0,
        ]);
    }

    /**
     * Rotates the left side of the cube 90 degrees clockwise.
     * If $invert is true, rotation is counter-clockwise.
     *
     * @param bool $invert
     * @return RubiksCube
     */
    public function back($invert = false)
    {
        return $this->doTurn([
            'faces' => [self::WHITE, self::GREEN, self::YELLOW, self::BLUE],
            'exclude' => self::ORANGE,
            'invert' => $invert,
            'rows' => true,
            'index' => 2,
        ]);
    }

    /**
     * @return Collection
     */
    public function getState(): Collection
    {
        return $this->state;
    }

    /**
     * Dumps the structure of the cube in a human-readable fashion.
     * Intended for debugging.
     */
    public function dump()
    {
        echo "Red\n";
        $this->printFace(self::RED);
        echo "Green\n";
        $this->printFace(self::GREEN);
        echo "Blue\n";
        $this->printFace(self::BLUE);
        echo "Yellow\n";
        $this->printFace(self::YELLOW);
        echo "White\n";
        $this->printFace(self::WHITE);
        echo "Orange\n";
        $this->printFace(self::ORANGE);

        return $this;
    }

    protected function printFace($face)
    {
        foreach ($this->state[$face] as $item) {
            foreach ($item as $item2) {
                echo " {$item2}";
            }
            echo "\n";
        }

        echo "\n";
    }
}
