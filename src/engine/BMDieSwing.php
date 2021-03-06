<?php
/**
 * BMDieSwing: Code specific to swing dice
 *
 * @author Julian
 */

/**
 * This class contains all the logic to do with requesting and setting swing values
 *
 * @property      char  $swingType         Swing type
 * @property      int   $swingValue        Swing value
 * @property      int   $swingMax          Maximum possible value of this swing type
 * @property      int   $swingMin          Minimum possible value of this swing type
 * @property-read bool  $needsSwingValue   Flag indicating whether a swing value is still needed
 * @property-read bool  $valueRequested    Flag indicating whether a swing request has been sent to the parent
 */
class BMDieSwing extends BMDie {
    public $swingType;
    public $swingValue;  // this is ALWAYS the value chosen by the player
    public $swingMax;
    public $swingMin;
    protected $needsSwingValue = TRUE;
    protected $valueRequested = FALSE;

    // Don't really like putting data in the code, but where else
    // should it go?
    //
    // Should be a constant, but that isn't allowed. Instead, we wrap
    // it in a method
    private static $swingRanges = array(
        "R"	=> array(2, 16),
        "S"	=> array(6, 20),
        "T"	=> array(2, 12),
        "U"	=> array(8, 30),
        "V"	=> array(6, 12),
        "W"	=> array(4, 12),
        "X"	=> array(4, 20),
        "Y"	=> array(1, 20),
        "Z"	=> array(4, 30));

    public static function swing_range($type) {
        if (array_key_exists($type, self::$swingRanges)) {
            return self::$swingRanges[$type];
        }
        return NULL;
    }

    public function init($type, array $skills = NULL) {
        $this->min = 1;

        $this->divisor = 1;
        $this->remainder = 0;

        $this->needsSwingValue = TRUE;
        $this->valueRequested = FALSE;

        $this->swingType = $type;

        $range = $this->swing_range($type);
        if (is_null($range)) {
            throw new UnexpectedValueException("Invalid swing type: $type");
        }
        $this->swingMin = $range[0];
        $this->swingMax = $range[1];

        $this->add_multiple_skills($skills);
    }

    public static function create($recipe, array $skills = NULL) {

        if (!is_string($recipe) || strlen($recipe) != 1 ||
            ord("R") > ord($recipe) || ord($recipe) > ord("Z")) {
            throw new UnexpectedValueException("Invalid recipe: $recipe");
        }

        $die = new BMDieSwing;

        $die->init($recipe, $skills);

        return $die;

    }

    public function activate() {
        $newDie = clone $this;

        if (!$this->does_skip_swing_request()) {
            // The clone is the one going into the game, so it's the one
            // that needs a swing value to be set.
            $this->ownerObject->request_swing_values(
                $newDie,
                $newDie->swingType,
                $newDie->playerIdx
            );
            $newDie->valueRequested = TRUE;
        }

        $this->ownerObject->add_die($newDie);
    }

    public function roll($isTriggeredByAttack = FALSE, $isSubdie = FALSE) {
        if ($this->needsSwingValue && !isset($this->max)) {
            if (!$this->valueRequested) {
                $this->ownerObject->request_swing_values(
                    $this,
                    $this->swingType,
                    $this->playerIdx
                );
                $this->valueRequested = TRUE;
            }
        } else {
            parent::roll($isTriggeredByAttack, $isSubdie);
        }
    }

    // Print long description
    public function describe($isValueRequired = FALSE) {
        if (!is_bool($isValueRequired)) {
            throw new InvalidArgumentException('isValueRequired must be boolean');
        }

        $skillStr = '';
        if (count($this->skillList) > 0) {
            foreach (array_keys($this->skillList) as $skill) {
                if (('Mood' != $skill) && ('Mad' != $skill)) {
                    $skillStr .= "$skill ";
                }
            }
        }

        $moodStr = '';
        if ($this->has_skill('Mad')) {
            $moodStr = ' Mad';
        } elseif ($this->has_skill('Mood')) {
            $moodStr = ' Mood';
        }

        $sideStr = '';
        if (isset($this->max)) {
            $sideStr = " (with {$this->max} side";
            if ($this->max != 1) {
                $sideStr .= 's';
            }
            $sideStr .= ')';
        }

        $valueStr = '';
        if ($isValueRequired && isset($this->value)) {
            $valueStr = " showing {$this->value}";
        }

        $result = "{$skillStr}{$this->swingType}{$moodStr} Swing Die{$sideStr}{$valueStr}";

        return $result;
    }

    public function set_swingValue($swingList) {
        $valid = TRUE;

        if (!array_key_exists($this->swingType, $swingList)) {
            return FALSE;
        }

        $sides = (int)$swingList[$this->swingType];

        if ($sides < $this->swingMin || $sides > $this->swingMax) {
            return FALSE;
        }

        if ($valid) {
            $this->swingValue = $sides;
            $this->needsSwingValue = FALSE;
            $this->valueRequested = FALSE;
            $this->max = $sides;
            $this->scoreValue = $sides;
        }

        return $valid;
    }

    public function getDieTypes() {
        $typesList = array();
        $typesList[$this->swingType . ' Swing'] = array(
            'code' => $this->swingType,
            'swingMin' => $this->swingMin,
            'swingMax' => $this->swingMax,
            'description' =>
                $this->swingType . ' Swing Dice can be any die between ' .
                $this->swingMin . ' and ' . $this->swingMax . '. Swing Dice ' .
                'are allowed to be any integral size between their upper and ' .
                'lower limit, including both ends, and including nonstandard ' .
                'die sizes like 17 or 9. Each player chooses his or her ' .
                'Swing Die in secret at the beginning of the match, and ' .
                'thereafter the loser of each round may change their Swing ' .
                'Die between rounds. If a character has any two Swing Dice ' .
                'of the same letter, they must always be the same size.',
        );
        return $typesList;
    }
}
