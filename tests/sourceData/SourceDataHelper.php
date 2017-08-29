<?php

class SourceDataHelper
{

    public static function getDataIndexedL1()
    {
        $o0 = new List1('test root');
        Leaf1::resetId();
        for ($x = 0; $x < 5; $x++) {
            $o0->appendObject(new Leaf1('test ' . $x));
        }
        return $o0;
    }

    public static function getDataNamedL1()
    {
        $o0 = new List1('test');
        $names = ['my_value_1', 'my_value_2', 'my_value_3'];
        Leaf1::resetId();
        foreach ($names as $name) {
            $o0->appendObject(new Leaf1('test ' . $name), $name);
        }
        return $o0;
    }

    public static function getDataIndexedL2()
    {
        $o0 = new List1('test');
        Leaf1::resetId();
        for ($x = 0; $x < 5; $x++) {
            $o1 = new List1('test ' . $x);
            for ($y = 0; $y < 5; $y++) {
                $o1->appendObject(new Leaf1('test ' . $x . '.' . $y));
            }
            $o0->appendObject($o1);
        }
        return $o0;
    }

    public static function getDataNamedL2()
    {
        $o0 = new List1('test');
        $names = ['my_value_1', 'my_value_2', 'my_value_3'];
        Leaf1::resetId();
        foreach ($names as $name) {
            $oL2 = new List1('test ' . $name);
            for ($x = 1; $x < 6; $x++) {
                $nameL2 = $name . '_' . $x;
                $oL2->appendObject(new Leaf1('test ' . $nameL2), $nameL2);
            }
            $o0->appendObject($oL2, $name);
        }
        return $o0;
    }

}