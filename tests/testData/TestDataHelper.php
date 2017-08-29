<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 29/08/17
 * Time: 23:08
 */

class TestDataHelper
{
    public static function getTestData1($withParentId = FALSE)
    {
        $compareArray = [];
        $count = 0;
        for ($x = 1; $x < 4; $x++) {
            $parentId = 'ID#' . $count;
            $compareArrayL2 = [
                'id' => $parentId,
                'name' => 'test my_value_' . $x,
                'content' => []
            ];
            $count++;
            for ($y = 1; $y < 6; $y++) {
                $childLevel = [
                    'id' => 'ID#' . $count,
                    'name' => 'test my_value_' . $x . '_' . $y,
                ];

                if ($withParentId) {
                    $childLevel['parent-id'] = $parentId;
                }

                $compareArrayL2['content'][] = $childLevel;
                $count++;
            }

            $compareArray[] = $compareArrayL2;
        }
        return $compareArray;
    }

    public static function getTestData2(){
        $count = 1;
        $result = [];
        for ($x = 1; $x < 4; $x++) {

            for ($y = 1; $y < 6; $y++) {
                $result[] = ['myId'=>'ID#' . $count];
                $count++;
            }
            $count++;
        }
        return $result;
    }

}