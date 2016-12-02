<?php
/*

1. 10x10 表格，撒50个豆子
2. 每个格子三种状态：空，有豆，墙壁
3. 吃豆人可能的动作：上、下、左、右、随机移动、不动、吃豆
4. 积分规则：吃到豆：10，移动：0，吃空：-1，撞墙：-5
5. 满分500
*/

$grid = array();

// 1. 随机生成200个吃豆人
// 2. 进行1000场比赛（每次随机撒豆子），算出每个吃豆人的平均分

ini_set('memory_limit', '2024M');
date_default_timezone_set('Asia/Shanghai');

class inherit
{
    private $generation         = 1000;   // 进化的代数
    private $gamesPerGeneration = 100;  // 每一代进行的比赛场次
    private $stepsPerGame       = 200;  // 每场比赛走的步数
    private $beanPersonCount    = 200;  // 每一代吃豆人的数量
    private $gridSize           = 10;   // 矩阵的大小

    private $beanPersons = array(); // 二维数组，index为代数，0为第一个随机策略

    /*
     * 随机生成一个10x10的矩阵，并随机赋值50个1(有豆)，50个0(无豆)
     */
    public function generateGame()
    {
        $cnt = 0;
        $grid = array();
        for ($i = 0; $i < $this->gridSize * $this->gridSize; $i++) {
            if ($i < 50) {
                $grid[$i] = 1;
            } else {
                $grid[$i] = 0;
            }
        }

        shuffle($grid);

        $grid = array_chunk($grid, 10);

        // $grid = json_decode("[[1,0,1,1,0,1,0,1,0,0],[0,0,0,1,0,1,0,1,1,0],[0,1,0,1,1,1,0,0,1,1],[1,0,0,1,0,1,0,1,1,1],[0,0,1,1,0,0,1,1,1,1],[0,1,0,1,1,1,0,0,0,0],[0,0,0,1,0,0,1,0,0,1],[1,1,0,0,0,0,0,1,0,1],[0,1,1,0,1,1,1,1,0,1],[0,1,0,1,1,0,0,1,0,1]]", TRUE);

        return $grid;
    }

    /*
     * 随机生产第0代吃豆人的生存策略
     */
    public function generateBeanPersons()
    {
        $person = '';
        for($i = 0; $i < 243; $i++) {
            $person .= rand(0, 5);
        }

        // $person = "231200111221312224051114300435302422533000141304213335105243123400103503511152513340201220542223333033425044235004124250244511345052445443210211024553522414542452140025350312524313010351150440054151121102455230031011221305314223430551230103211";
        return $person;
    }

    /*
     * 评分函数
     *  00 00 00 00 00   (00: 无豆，01：有豆，10：墙)
     *  上 下  左 右 中
     * @param $person 生存策略
     * @param $game   比赛矩阵
     * @return 评分
     */
    public function rating($person, $game)
    {
        // echo "xxxxxxxxxxxxxxxx ";
        // var_dump($person, $game);

        $idx = 0;
        $statusString2statusIdx = array();
        for ($i=0; $i < 3; $i++) {
            for ($j=0; $j < 3; $j++) {
                for ($k=0; $k < 3; $k++) {
                    for ($l=0; $l < 3; $l++) {
                        for ($m=0; $m < 3; $m++) {
                            $key = sprintf("%02d", decbin($i)) . sprintf("%02d", decbin($j)) . sprintf("%02d", decbin($k)) . sprintf("%02d", decbin($l)) . sprintf("%02d", decbin($m));
                            $statusString2statusIdx[$key] = $idx;
                            $idx++;
                        }
                    }
                }
            }
        }

        // 整数索引到字符串索引
        $statusIdx2statusString = array_flip($statusString2statusIdx);
        // var_dump($statusString2statusIdx);exit;

        $randPosX = rand(0,9);
        $randPosY = rand(0,9);

        // echo "pos: x {$randPosX} y {$randPosY}\n";

        $rating = 0;
        for ($step=0; $step < $this->stepsPerGame; $step++) { // 走两百步
            // echo "step {$step}------------------\n";
            // echo "x:{$randPosX}, y: {$randPosY}\n";
            $up     = 0;
            $down   = 0;
            $left   = 0;
            $right  = 0;
            $middle = 0;

            // 当前位置状态
            $middle = $game[$randPosX][$randPosY];

            // 判断上下左右是否是墙壁
            ($randPosX == 0) && ($left = 2);
            ($randPosX == 9) && ($right = 2);
            ($randPosY == 0) && ($up = 2);
            ($randPosY == 9) && ($down = 2);

            if ($randPosX > 0 && $randPosX < 9) {
                $left  = $game[$randPosX - 1][$randPosY];
                $right = $game[$randPosX + 1][$randPosY];
            }

            if ($randPosY > 0 && $randPosY < 9) {
                $up   = $game[$randPosX][$randPosY - 1];
                $down = $game[$randPosX][$randPosY + 1];
            }

            $statusString = sprintf("%02d", decbin($up)) . sprintf("%02d", decbin($down)) . sprintf("%02d", decbin($left)) . sprintf("%02d", decbin($right)) . sprintf("%02d", decbin($middle));
            $statusInx = $statusString2statusIdx[$statusString];
            // echo "status: {$statusString}  {$statusInx}\n";

            $action = strval($person)[$statusInx];
            // echo "action: {$action}\n";
            switch ($action) {
                case Action::UP:
                    ($up == 2) && ($rating -= 5);
                    (($up == 1) || ($up == 0)) && ($randPosY -= 1);
                    break;
                case Action::DOWN:
                    ($down == 2) && ($rating -= 5);
                    (($down == 1) || ($down == 0)) && ($randPosY += 1);
                    break;
                case Action::LEFT:
                    ($left == 2) && ($rating -= 5);
                    (($left == 1) || ($left == 0)) && ($randPosX -= 1);
                    break;
                case Action::RIGHT:
                    ($right == 2) && ($rating -= 5);
                    (($right == 1) || ($right == 0)) && ($randPosX += 1);
                    break;
                case Action::RANDOM:
                    $dir = rand(0, 3);

                    ($dir == Action::UP)    && ($up == 2)    && ($rating -= 5);
                    ($dir == Action::DOWN)  && ($down == 2)  && ($rating -= 5);
                    ($dir == Action::LEFT)  && ($left == 2)  && ($rating -= 5);
                    ($dir == Action::RIGHT) && ($right == 2) && ($rating -= 5);

                    ($dir == Action::UP)    && ($up != 2)    && ($randPosY -= 1);
                    ($dir == Action::DOWN)  && ($down != 2)  && ($randPosY += 1);
                    ($dir == Action::LEFT)  && ($left != 2)  && ($randPosX -= 1);
                    ($dir == Action::RIGHT) && ($right != 2) && ($randPosX += 1);
                    break;
                case Action::EAT:
                    ($middle == 1) && ($rating += 10);
                    ($middle == 0) && ($rating -= 1);

                    if ($middle == 0) // 吃空之后随机移动一步
                    {
                        $dir = rand(0, 3);

                        ($dir == Action::UP)    && ($up == 2)    && ($rating -= 5);
                        ($dir == Action::DOWN)  && ($down == 2)  && ($rating -= 5);
                        ($dir == Action::LEFT)  && ($left == 2)  && ($rating -= 5);
                        ($dir == Action::RIGHT) && ($right == 2) && ($rating -= 5);

                        ($dir == Action::UP)    && ($up != 2)    && ($randPosY -= 1);
                        ($dir == Action::DOWN)  && ($down != 2)  && ($randPosY += 1);
                        ($dir == Action::LEFT)  && ($left != 2)  && ($randPosX -= 1);
                        ($dir == Action::RIGHT) && ($right != 2) && ($randPosX += 1);
                    }

                    $game[$randPosX][$randPosY] = 0;
                    break;
                default:
                    break;
            }
            // echo "rating: {$rating}\n";
        }
        // echo "rating: {$rating}\n";

        return $rating;
    }

    public function run()
    {
        $this->beanPersons[0] = array();

        echo date('Y-m-d H:i:s') . " begin generate init 200 bean person\n";
        // 生成200个初始吃豆人
        for ($i=0; $i < $this->beanPersonCount ; $i++) {
            $this->beanPersons[0][$i]['strategy'] = $this->generateBeanPersons();
            $this->beanPersons[0][$i]['rating'] = 0;
        }
        echo date('Y-m-d H:i:s') . " end generate init 200 bean person\n";

        for ($g=0; $g < $this->generation; $g++) { // 进行100代
            echo date('Y-m-d H:i:s') . " generation $g ... \n";
            for ($j=0; $j < $this->gamesPerGeneration; $j++) { // 进行100场比赛
                // echo date('Y-m-d H:i:s') . " begin generate game $j\n";
                $game = $this->generateGame(); // 随机生成比赛
                // echo date('Y-m-d H:i:s') . " end generate game $j\n";
                for ($k=0; $k < $this->beanPersonCount; $k++) {
                    // echo date('Y-m-d H:i:s') . " begin rating bean person $k\n";
                    $rate = $this->rating($this->beanPersons[$g][$k]['strategy'], $game);
                    // echo date('Y-m-d H:i:s') . " end rating bean person $k\n";
                    $this->beanPersons[$g][$k]['rating'] = intval(($rate + $this->beanPersons[$g][$k]['rating']) / 2);
                }
            }

            echo date('Y-m-d H:i:s') . " begin generate next generation\n";

            $sum = 0;
            $listPoint = array(0);
            for ($m=0; $m < $this->beanPersonCount; $m++) {
                $sum += $this->beanPersons[$g][$m]['rating'];
                $sum += 1000; // 负数归正
                array_push($listPoint, $sum);
            }
            $listPointCnt = count($listPoint);

            echo "rating sum: {$sum}\n";
            // 生成下一代200个吃豆人
            for ($l=0; $l < $this->beanPersonCount/2; $l++) {
                $p1 = 0;
                $p2 = 0;

                // 选择父亲
                $randNum = rand(0, $sum);
                for ($i = 0; $i < $listPointCnt - 1; $i++)
                {
                    if ($randNum >= $listPoint[$i] && $randNum <= $listPoint[$i +1])
                    {
                        $p1 = $i;
                    }
                }

                do{
                    // 选择母亲
                    $randNum = rand(0, $sum);
                    for ($i = 0; $i < $listPointCnt - 1; $i++)
                    {
                        if ($randNum >= $listPoint[$i] && $randNum <= $listPoint[$i +1])
                        {
                            $p2 = $i;
                        }
                    }
                }while ($p2 == $p1);

                echo "father: {$p1}, mother: {$p2}\n";
                echo "father rating: {$this->beanPersons[$g][$p1]['rating']}, mother rating: {$this->beanPersons[$g][$p2]['rating']}";

                // 选择切断位
                $slicePos = rand(1, 242);
                $slice1 = substr($this->beanPersons[$g][$p1]['strategy'], 0, $slicePos);
                $slice2 = substr($this->beanPersons[$g][$p2]['strategy'], $slicePos);
                $c1 = $slice1 . $slice2;
                $slice1 = substr($this->beanPersons[$g][$p1]['strategy'], $slicePos);
                $slice2 = substr($this->beanPersons[$g][$p2]['strategy'], 0, $slicePos);
                $c2 = $slice2 . $slice1;

                // c1 有两位随机变异
                $variationPos = rand(0, 242);
                $c1 = substr_replace($c1, rand(0, 6), $variationPos, 1);
                $variationPos = rand(0, 242);
                $c1 = substr_replace($c1, rand(0, 6), $variationPos, 1);

                // c2 有两位随机变异
                $variationPos = rand(0, 242);
                $c2 = substr_replace($c2, rand(0, 6), $variationPos, 1);
                $variationPos = rand(0, 242);
                $c2 = substr_replace($c2, rand(0, 6), $variationPos, 1);

                $this->beanPersons[$g + 1][] = array(
                    'strategy' => $c1,
                    'rating' => 0
                );

                $this->beanPersons[$g + 1][] = array(
                    'strategy' => $c2,
                    'rating' => 0
                );
            }
            echo date('Y-m-d H:i:s') . " end generate next generation\n";
        }
        // var_dump($this->beanPersons);
        foreach ($this->beanPersons as $gen => $v) {
            echo "generation {$gen}: \n";
            foreach ($v as $val) {
                echo "{$val['rating']} \n";
            }
        }
    }
}

class GridStatus
{
    const EMPTYY = 0; // 空
    const BEAN  = 1; // 有豆
    const WALL  = 2; // 墙壁
}

class Action
{
    const UP     = 0;
    const DOWN   = 1;
    const LEFT   = 2;
    const RIGHT  = 3;
    const RANDOM = 4;
    const EAT    = 5;
}

$o = new inherit();
$o->run();