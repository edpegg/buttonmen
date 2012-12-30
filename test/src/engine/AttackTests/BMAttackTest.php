<?php

require_once "engine/BMAttack.php";

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-21 at 15:10:47.
 */
class BMAttackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BMAttack
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = BMAttTesting::get_instance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object->clear_dice();
        $this->object->clear_log();
    }

    /**
     * @covers BMAttack::get_instance
     */
    public function testGet_instance()
    {
        $test1 = BMAttack::get_instance();
        $test2 = BMAttack::get_instance();

        $this->assertTrue($test1 === $test2);
    }

    /**
     * @covers BMAttack::add_die
     */
    public function testAdd_die()
    {
        $die1 = new BMDie;
        $die2 = new BMDie;
        $die3 = new BMDie;


        $this->object->add_die($die1);

        $dlist = PHPUnit_Framework_Assert::readAttribute($this->object, "validDice");

        $this->assertNotEmpty($dlist);
        $this->assertEquals(1, count($dlist));
        $this->assertContains($die1, $dlist);

        // duplication?
        $this->object->add_die($die1);

        $dlist = PHPUnit_Framework_Assert::readAttribute($this->object, "validDice");

        $this->assertNotEmpty($dlist);
        $this->assertEquals(1, count($dlist));
        $this->assertContains($die1, $dlist);

        // multiple dice
        $this->object->add_die($die2);
        $this->object->add_die($die3);
        $dlist = PHPUnit_Framework_Assert::readAttribute($this->object, "validDice");

        $this->assertNotEmpty($dlist);
        $this->assertEquals(3, count($dlist));
        $this->assertContains($die1, $dlist);
        $this->assertContains($die2, $dlist);
        $this->assertContains($die3, $dlist);
        
        // duplication in bigger list
        $this->object->add_die($die3);

        $dlist = PHPUnit_Framework_Assert::readAttribute($this->object, "validDice");

        $this->assertNotEmpty($dlist);
        $this->assertEquals(3, count($dlist));
        $this->assertContains($die1, $dlist);
        $this->assertContains($die2, $dlist);
        $this->assertContains($die3, $dlist);


    }

    /**
     * @covers BMAttack::help_bounds
     */
    public function testHelp_bounds()
    {
        $nohelp = array(0);
        $smallhelp = array(1, 2, 3);
        $bighelp = array(1, 2, 3, 4, 5, 6);
        $neghelp = array(-4, -3, -2, -1);
        $widehelp = array(-2, -1, 0, 1, 2);

        // no help
        $helpvals = array();

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(0, $bounds[0]);
        $this->assertEquals(0, $bounds[1]);

        // help, but not helpful
        $helpvals = array($nohelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(0, $bounds[0]);
        $this->assertEquals(0, $bounds[1]);

        // lots of lack of help
        $helpvals = array($nohelp, $nohelp, $nohelp, $nohelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(0, $bounds[0]);
        $this->assertEquals(0, $bounds[1]);

        // various one-die scenarios
        $helpvals = array($smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(3, $bounds[1]);
        
        $helpvals = array($bighelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);


        $helpvals = array($neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);


        $helpvals = array($widehelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-2, $bounds[0]);
        $this->assertEquals(2, $bounds[1]);

        // combinations

        $helpvals = array($smallhelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        $helpvals = array($smallhelp, $bighelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(9, $bounds[1]);

        // mix in some non-help (which shouldn't happen)

        $helpvals = array($nohelp, $smallhelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        $helpvals = array($smallhelp, $nohelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        $helpvals = array($smallhelp, $smallhelp, $nohelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(1, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        // negatives

        $helpvals = array($neghelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-8, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);

        $helpvals = array($neghelp, $nohelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-8, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);

        $helpvals = array($nohelp, $neghelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-8, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);

        $helpvals = array($neghelp, $neghelp, $nohelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-8, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);

        $helpvals = array($neghelp, $neghelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-12, $bounds[0]);
        $this->assertEquals(-1, $bounds[1]);



        // mix pos and heg
        $helpvals = array($smallhelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(3, $bounds[1]);

        $helpvals = array($neghelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(3, $bounds[1]);

        $helpvals = array($smallhelp, $neghelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        $helpvals = array($neghelp, $smallhelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        $helpvals = array($smallhelp, $smallhelp, $neghelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-4, $bounds[0]);
        $this->assertEquals(6, $bounds[1]);

        // mix things up with something that spans zero

        $helpvals = array($smallhelp, $widehelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-2, $bounds[0]);
        $this->assertEquals(5, $bounds[1]);

        $helpvals = array($neghelp, $widehelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-6, $bounds[0]);
        $this->assertEquals(2, $bounds[1]);


        $helpvals = array($bighelp, $neghelp, $widehelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-6, $bounds[0]);
        $this->assertEquals(8, $bounds[1]);

        $helpvals = array($widehelp, $nohelp, $neghelp, $widehelp, $smallhelp);

        $bounds = $this->object->help_bounds($helpvals);

        $this->assertEquals(2, count($bounds));
        $this->assertEquals(-8, $bounds[0]);
        $this->assertEquals(7, $bounds[1]);
    }

    /**
     * @covers BMAttack::collect_contributions
     * @todo   Implement testCollect_contributions().
     */
    public function testCollect_contributions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers BMAttack::find_attack
     */
    public function testFind_attack()
    {
        $att = BMAttack::get_instance();
        $this->assertFalse($att->find_attack(new DummyGame));
    }

    /**
     * @covers BMAttack::validate_attack
     */
    public function testValidate_attack()
    {
        $att = BMAttack::get_instance();
        $this->assertFalse($att->validate_attack(new DummyGame,
                                                 array(new BMDie),
                                                 array(new BMDie)));
    }

    /**
     * @covers BMAttack::commit_attack
     */
    public function testCommit_attack()
    {
        $att = BMAttack::get_instance();
        $this->assertFalse($att->commit_attack(new DummyGame,
                                               array(new BMDie),
                                               array(new BMDie)));
    }


    /**
     * @covers BMAttack::search_ovm_helper
     */
    public function testSearch_ovm_helper() {
        
        // need to get at a protected method
        $attack = BMAttTesting::get_instance();
        
        $attackLog = array();
        $aRef = &$attackLog;

        // basic failure conditions
        $comparetrue = function ($a, $b, $c) use (&$aRef) { 
            $aRef[] = array($b, $c);
            return TRUE;
        };
        $comparefalse = function ($a, $b, $c) use (&$aRef) {
            $aRef[] = array($b, $c);            
            return FALSE;
        };

        $this->assertFalse($attack->test_ovm_helper("game", array(), array(), $comparetrue));
        $this->assertFalse($attack->test_ovm_helper("game", array(new BMDie), array(), $comparetrue));
        $this->assertFalse($attack->test_ovm_helper("game", array(), array(new BMDie), $comparetrue));
        $this->assertTrue($attack->test_ovm_helper("game", array(new BMDie), array(new BMDie), $comparetrue));


        $attackLog = array();

        // confirm that the search stops on the first hit
        $attack->test_ovm_helper("game", array(1, 2, 3), array('A', 'B', 'C'), $comparetrue);

        $this->assertEquals(1, count($attackLog));

        $attackLog = array();

        // and that it iterates over everything if it doesn't.
        // one is three rounds
        // many is three (at 1) + three (at 2) + 1 (at 3) 
        // so 21 in all
        $attack->test_ovm_helper("game", array(1, 2, 3), array('A', 'B', 'C'), $comparefalse);

        $this->assertEquals(21, count($attackLog));

        
        // check the coverage
        $check = array();
        for ($i = 1; $i <= 3; $i++) {
            $check[$i] = array();
            foreach(array('A', 'B', 'C', 'AB', 'BC', 'AC', 'ABC') as $key) {
                $check[$i][$key] = 0;
            }
        }

        foreach ($attackLog as $att) {
            $check[$att[0][0]][join($att[1])]++;
        }

        for ($i = 1; $i <= 3; $i++) {
            foreach ($check[$i] as $hit) {
                $this->assertEquals(1, $hit);
            }
        }
    }

    /**
     * @covers BMAttack::search_onevone
     */
    public function testSearch_onevone() {
        // The validate_attack method is rigged to return true if
        // $game is true
        $aList = array(1, 2, 3);
        $dList = array('A', 'B', 'C');
        $this->assertTrue($this->object->test_ovo(TRUE, $aList, $dList));

        $this->assertEquals(1, count($this->object->attackLog));

        $this->object->clear_log();

        // search the whole space 
        $this->assertFalse($this->object->test_ovo(FALSE, $aList, $dList));

        $this->assertEquals(9, count($this->object->attackLog));

        // check the coverage
        $check = array();
        for ($i = 1; $i <= 3; $i++) {
            $check[$i] = array();
            foreach(array('A', 'B', 'C') as $key) {
                $check[$i][$key] = 0;
            }
        }

        foreach ($this->object->attackLog as $att) {
            $check[$att[0][0]][join($att[1])]++;
        }

        for ($i = 1; $i <= 3; $i++) {
            foreach ($check[$i] as $hit) {
                $this->assertEquals(1, $hit);
            }
        }
    }

    /**
     * @covers BMAttack::search_onevmany
     * @depends testSearch_ovm_helper
     */
    public function testSearch_onevmany() {
        // The validate_attack method is rigged to return true if
        // $game is true
        $this->object->clear_log();
     
        $aList = array(1, 2, 3);
        $dList = array('A', 'B', 'C');
        $this->assertTrue($this->object->test_ovm(TRUE, $aList, $dList));

        $this->assertEquals(1, count($this->object->attackLog));

        $this->object->clear_log();

        // search the whole space 
        $this->assertFalse($this->object->test_ovm(FALSE, $aList, $dList));

        $this->assertEquals(21, count($this->object->attackLog));

        // check the coverage
        $check = array();
        for ($i = 1; $i <= 3; $i++) {
            $check[$i] = array();
            foreach(array('A', 'B', 'C', 'AB', 'AC', 'BC', 'ABC') as $key) {
                $check[$i][$key] = 0;
            }
        }

        foreach ($this->object->attackLog as $att) {
            $check[$att[0][0]][join($att[1])]++;
        }

        for ($i = 1; $i <= 3; $i++) {
            foreach ($check[$i] as $hit) {
                $this->assertEquals(1, $hit);
            }
        }
    }

    /**
     * @covers BMAttack::search_manyvone
     * @depends testSearch_ovm_helper
     */
    public function testSearch_manyvone() {
        // The validate_attack method is rigged to return true if
        // $game is true
        $this->object->clear_log();
     
        $aList = array('A', 'B', 'C');
        $dList = array(1, 2, 3);
        $this->assertTrue($this->object->test_mvo(TRUE, $aList, $dList));

        $this->assertEquals(1, count($this->object->attackLog));

        $this->object->clear_log();

        // search the whole space 
        $this->assertFalse($this->object->test_mvo(FALSE, $aList, $dList));

        $this->assertEquals(21, count($this->object->attackLog));

        // check the coverage
        $check = array();
        foreach(array('A', 'B', 'C', 'AB', 'AC', 'BC', 'ABC') as $key) {
            $check[$key] = array();
            for ($i = 1; $i <= 3; $i++) {
                $check[$key][$i] = 0;
            }
        }

        foreach ($this->object->attackLog as $att) {
            $check[join($att[0])][$att[1][0]]++;
        }

        foreach(array('A', 'B', 'C', 'AB', 'AC', 'BC', 'ABC') as $key) {
            foreach ($check[$key] as $hit) {
                $this->assertEquals(1, $hit);
            }
        }
    }

}


