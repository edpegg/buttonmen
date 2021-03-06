<?php
/**
 * BMAttackPower: code specific to power attacks
 *
 * @author Julian
 */

/**
 * This class contains code specific to power attacks
 */
class BMAttackPower extends BMAttack {
    public $type = 'Power';

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    public function validate_attack($game, array $attackers, array $defenders, $helpValue = NULL) {
        $this->validationMessage = '';

        if (1 != count($attackers)) {
            $this->validationMessage = 'There must be exactly one attacking die for a power attack.';
            return FALSE;
        }

        if (1 != count($defenders)) {
            $this->validationMessage = 'There must be exactly one target die for a power attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        if (is_null($helpValue)) {
            $bounds = $this->help_bounds(
                $this->collect_helpers($game, $attackers, $defenders),
                $this->collect_firing_maxima($attackers)
            );
        } else {
            $bounds = array($helpValue, $helpValue);
        }

        $att = $attackers[0];
        $def = $defenders[0];

        foreach ($att->attack_values($this->type) as $aVal) {
            $validationArray = array();
            // james: 'isDieLargeEnough' is required for the case of fired-up dice
            $validationArray['isDieLargeEnough'] =
                $att->max >= $def->defense_value($this->type);
            $validationArray['isValLargeEnough'] =
                $aVal + $bounds[1] >= $def->defense_value($this->type);
            // james: 'isIncreasedValueValid' is required for the case of fired-up dice
            if ($helpValue) {
                $validationArray['isIncreasedValueValid'] =
                    ($aVal + $helpValue <= $att->max);
            } else {
                $validationArray['isIncreasedValueValid'] = TRUE;
            }
            $validationArray['isValidAttacker'] =
                $att->is_valid_attacker($attackers);
            $validationArray['isValidTarget'] =
                $def->is_valid_target($defenders);

            $this->validationMessage =
                $this->get_validation_message($validationArray, $helpValue);

            if (empty($this->validationMessage)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        // attacker skills
        if ($att->has_skill('Shadow')) {
            $this->validationMessage = 'Shadow dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Konstant')) {
            $this->validationMessage = 'Konstant dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Fire')) {
            $this->validationMessage = 'Fire dice cannot perform power attacks.';
            return FALSE;
        }

        if ($att->has_skill('Queer') && (1 == $att->value % 2)) {
            $this->validationMessage = 'Odd queer dice cannot perform power attacks.';
            return FALSE;
        }

        // defender skills
        if ($def->has_Skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be attacked by power attacks.';
            return FALSE;
        }

        return TRUE;
    }

    protected function get_validation_message($validationArray, $helpValue) {
        if (!$validationArray['isDieLargeEnough']) {
            return 'Attacking die size must be at least as large as target die value';
        }

        if (!$validationArray['isValLargeEnough']) {
            if ($helpValue) {
                return 'Fire dice not turned down enough.';
            } else {
                return 'Attacking die value must be at least as large as target die value';
            }
        }

        if (!$validationArray['isIncreasedValueValid']) {
            if (1 == $helpValue) {
                $helpValueUnit = 'point';
            } else {
                $helpValueUnit = 'points';
            }
            return 'Attacker cannot be fired up by ' .
                   $helpValue . ' ' . $helpValueUnit . '.';
        }

        if (!$validationArray['isValidAttacker']) {
            return 'Invalid attacking die';
        }

        if (!$validationArray['isValidTarget']) {
            return 'Invalid target die';
        }

        return '';
    }
}
