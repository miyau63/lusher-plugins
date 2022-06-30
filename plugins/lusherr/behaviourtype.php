<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question behaviour type for Lusher Test behaviour.
 *
 * @package    qbehaviour_lusher
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Question behaviour type information for Lusher Test behaviour.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_lusherr_type extends question_behaviour_type {
    public function is_archetypal() {
        return true;
    }

    public function allows_multiple_submitted_responses() {
        return false;
    }

    public function get_unused_display_options() {
        return '';
    }

    public function can_questions_finish_during_the_attempt()
    {
        return false;
    }

    public function summarise_usage(question_usage_by_activity $quba, question_display_options $options) {
        global $OUTPUT, $DB;


        $summarydata = parent::summarise_usage($quba, $options);
        $slots=$quba->get_slots();
        //print_r($slots);

        $slotquestion = $quba->get_response_summary($slots[0]);
        $summarydata[1] = array(
                'title' => 'ОТЗЫВ НА ОТВЕТ',
                'content' => html_writer::tag('h3',
                    $slotquestion),
            );
        $slotquestion = $quba->get_response_summary($slots[1]);
        $summarydata[2] = array(
            'title' => 'ОТЗЫВ НА ОТВЕТ',
            'content' => html_writer::tag('h3',
                $slotquestion),
        );


        /** @var  array Response summary in suitable manner */
        $resp=array();
        $resp[0] = $quba->get_response_summary($slots[0]);
        $resp[0] = str_replace(' ', '', $resp[0]);
        $resp[1] = $quba->get_response_summary($slots[1]);
        $resp[1] = str_replace(' ', '', $resp[1]);

        $generated_text=$this->lusher_estimate($resp, $quba->get_id());
        $summarydata['warning'] = array(
            'title' => 'ПРЕАМБУЛА',
            'content' => html_writer::tag('div',
               ' Тест Люшера позволяет оценить ряд показателей человека, которые собраны в несколько групп и обозначены как "+", "-", "x", "=" , "-" , "+-" ). Далее полужирным шрифтом изложены название и краткое описание групп оценок. 
               Обычным (светлым) шрифтом приведены полученные в рамках теста значения показателей, 
               которые однако не могут рассматриваться в качестве заключительных, безоговорочных и т.п.  
               Все полученные тестом оценки ответов подлежат верификации, подтверждению и окончательной 
               интерпретацией экспертами (сотрудниками кадровых служб, социологами, психологами и т.п)'),
        );


        $summarydata['get_your_response'] = array(
            'title' => 'Предварительная оценка полученных ответов (нуждается в уточнении профессионалом)',
            'content' => html_writer::tag('div',
                $generated_text),
        );




        return $summarydata;
    }

    protected  function lusher_estimate(array $resp, $qubaid){

        /** Divides the string with the initial sequence into pairs
         * @param $str string with initial sequence
         * @return array with pares: {position}->{pair}
         */
        function ParesDevide($str){
            for($i = 0; $i < 7;$i++)  $newarr[$i] = substr($str, $i, 2);
            return $newarr;
        }
        /** @var array Разделенная на пары 1 выборка */
        $rest1 = ParesDevide($resp[0]);
        /** @var array Разделенная на пары 2 выборка */
        $rest2=ParesDevide($resp[1]);

        /** @var  array Позиция пар во 2 массиве */
        $pos_pares=array();
        /** @var  string Строка с цифрами(из 1 ответа), которые не составили пар */
        $withoutneib1 = $resp[0];
        /** @var  string Строка с цифрами(из 2 ответа), которые не составили пар */
        $withoutneib2=$resp[1];
        /** @var  string|array Дополнительная переменная для отдельных символов */
        $symb = "";

        $resp[0]=' '.$resp[0];
        for($i = 0; $i < 7;$i++){
            $str= $rest2[$i];
            $str1= $rest1[$i];
            /** @var   array Позиция пары (из 2 массива) в (1) строке */
            $pos[$rest2[$i]] = strpos($resp[0],$str);
            if ($pos[$rest2[$i]]=='') {
                $n = strrev($str);
                $pos[$rest2[$i]] = strpos($resp[0],$n);
            }

            if ($pos[$rest2[$i]]!=0) {
                $pos_pares[$i]=$rest2[$i];
                $symb = [substr($rest2[$i],0,1),substr($rest2[$i],1,1)];
                $withoutneib1 = str_replace($symb, "", $withoutneib1);
                $withoutneib2 = str_replace($symb,  "", $withoutneib2);
            }
        }

        $resp[0]= ltrim($resp[0],' ');

        /** @var  array Разделенный массив цифер(во 2 строке), у которых не оказалось пары */
        $ans2=array();
        /** @var  array Разделенный массив цифер(в 1 строке), у которых не оказалось пары */
        $ans1=array();
        /** @var  array Массив новых позиции пар во 2 строке*/
        $pos_pares2=array();
        /** @var  array Массив новых позиции пар в 1 строке*/
        $pos_pares1=array();
        /** @var  array Массив новых позиции отдельных цифер во 2 строке*/
        $pos_singles2=array();
        /** @var  array Массив новых позиции пар или отдельных в 1 строке*/
        $pos_singles1=array();

        if(strlen($withoutneib2)>1) {
            for($i = 0; $i < strlen($withoutneib2)-1;$i++){
                // а вот тут я обработала помассивно все => теперь мы можем сделать позиции
                $ans2[$i] = substr($withoutneib2, $i, 2);
                $ans1[$i] = substr($withoutneib1, $i, 2);
            }
        }elseif(strlen($withoutneib2)===1){
            $pos_singles2[strpos($resp[1],$withoutneib2)] = $withoutneib2;
            $withoutneib2='';
            $pos_singles1[strpos($resp[0],$withoutneib1)] = $withoutneib1;
            $withoutneib1='';
        }

       /*Делим оставшиеся числа на пары, т.к. так точно будет куда логичнее рассматривать уже устоявшиеся пары
            переделаем первую функцию, чтобы искать по 2 массиву
            идем с конца в начало, чтобы верно расположить числа
        ну и делать мы это все будем только если у нас есть хоть одно число без пары*/
        if($withoutneib1<>"" or $withoutneib2<>"") {
            for ($i = count($ans2) - 1; $i >= 0; $i--) {
                if (array_search($ans2[$i], $rest2) <> "" or array_search($ans2[$i], $rest2) === 0) {
                    $pos_pares2[array_search($ans2[$i], $rest2)] = $ans2[$i];
                    $symb = [substr($ans2[$i], 0, 1), substr($ans2[$i], 1, 1)];
                    $withoutneib2 = str_replace($symb, "", $withoutneib2);
                    $i = $i - 1;
                }
            }

            for ($i = count($ans1) - 1; $i >= 0; $i--) {
                if (array_search($ans1[$i], $rest1) <> "" or array_search($ans1[$i], $rest1) === 0) {
                    $pos_pares1[array_search($ans1[$i], $rest1)] = $ans1[$i];
                    $symb = [substr($ans1[$i], 0, 1), substr($ans1[$i], 1, 1)];
                    $withoutneib1 = str_replace($symb, "", $withoutneib1);
                    $i = $i - 1;
                }
            }
        }


        /** Создание массива {position}->{number}
         * @param $str строка с цифрами без пар
         * @param $initOrder строка с исходной последовательностью
         * @return mixed разделенный массив: место в строке -> цифра
         */
        function SubSingles($str, $initOrder){
            for($i=0;$i<strlen($str);$i++){
                $symb = substr($str, $i, 1);
                $newarr[strpos($initOrder,$symb)] = $symb;
            }
            return $newarr;
        }
        /*Eсли остались числа => у чисел вообще нет пары и они все одиночки, а одиночки мы будем записывать по позициям в строке
         */
        if($withoutneib1<>"") $pos_singles1=SubSingles($withoutneib1,$resp[0]);

        if($withoutneib2<>"") $pos_singles2=SubSingles($withoutneib2,$resp[1]);


        //теперь мы замахнемся на оценку
        /** @var  array Строка с функциональными парами + */
        $estMatPlus=array();
        /** @var  array Строка с функциональными парами +- */
        $estMatPlusMin=array();
        /** @var  array Строка с функциональными парами - */
        $estMatMin=array();
        /** @var  array Строка с функциональными парами x */
        $estMatx=array();
        /** @var  array Строка с функциональными парами = */
        $estMatEq=array();
        /** @var  int exclamation mark counter */
        $exclamationCount=0;
        /** @var  int Флаг для нужных мне проверок*/
        $flag=0;

        //проверка на тот случай, когда в каком-то выборе стоят отдельно 2 одиночки, а в другом вы-ре вместе
        if(sizeof($pos_singles1)==2 && sizeof($pos_pares2)==1){//тогда тут надо оценивать обе ф-ции
            foreach($pos_pares2 as $k1=>$v1){
                foreach($pos_singles1 as $k=>$v){
                    if(substr($pos_pares2[$k1],1,0)==$v){
                        if($k===0) array_push($estMatPlus,$pos_singles1[0]);
                        if($k===1) array_push($estMatPlus,$pos_singles1[1]);
                        if($k===2) array_push($estMatx,$pos_singles1[2]);
                        if($k===3) array_push($estMatx,$pos_singles1[3]);
                        if($k===4) array_push($estMatEq,$pos_singles1[4]);
                        if($k===5) array_push($estMatEq,$pos_singles1[5]);
                        if($k===6) array_push($estMatMin,$pos_singles1[6]);
                        if($k===7) array_push($estMatMin,$pos_singles1[7]);
                        $flag = 10;
                    }
                }
            }
            //а теперь в зависимости от позиции эти штуки надо добавить в наш оценивающий массив $pos_pares_2
        }elseif(sizeof($pos_singles2)==2 && sizeof($pos_pares1)==1){
            //pos_singles будут обрабатываться отдельно в дальнейшем
            $f=0;
            foreach($pos_pares1 as $k1=>$v1){
                foreach($pos_singles2 as $k=>$v){
                    if(substr($pos_pares1[$k1],0,1)==$v){$f=$f+1;}}}
            if($f==2){
                if(isset($pos_pares1[0])) array_push($estMatPlus,$pos_pares1[0]);
                elseif(isset($pos_pares1[1])) array_push($estMatx,$pos_pares1[1]);
                elseif(isset($pos_pares1[2])) array_push($estMatx,$pos_pares1[2]);
                elseif(isset($pos_pares1[3])) array_push($estMatEq,$pos_pares1[3]);
                elseif(isset($pos_pares[4])) array_push($estMatEq,$pos_pares[4]);
                elseif(isset($pos_pares[5])) array_push($estMatEq,$pos_pares[5]);
                elseif(isset($pos_pares[6])) array_push($estMatMin,$pos_pares[6]);
                $flag = 10;}
        }

        //вот тут будет проверка на экстра-числа=> если есть экстра-число на 1 2 или 3 месте=> нужно все переоценивать во 2 раз
        if(substr($resp[1], 0, 1)==8 or substr($resp[1], 0, 1)==6 or substr($resp[1], 0, 1)==7){
            $exclamationCount=3;
            //так как число мб парой => нужно назначить + целой паре и приписать в пары
            if(array_key_exists(0,$pos_pares) or array_key_exists(0,$pos_pares2)){
                array_push($estMatPlus,substr($resp[1], 0,2));
                // удаляем эту пару для дальнейшей проверки
                unset($pos_pares2[0]);
                unset($pos_pares[0]);
            } else  array_push($estMatPlus,substr($resp[1], 0,1));
            //удаляем это число из всего того, где оно могло оказаться
            unset($pos_singles2[0]);
            $flag=5;
        }
        if(substr($resp[1], 1, 1)==8 or substr($resp[1], 1, 1)==6 or substr($resp[1], 1, 1)==7){
            $exclamationCount+=2;
            if(array_key_exists(1,$pos_pares)) $pos_singles2[2]=substr($resp[1], 2,1);
            //проверка $slots = ["0" => "1 4 3 2 6 7 5 8", "1" => "1 7 5 6 2 3 4 8"];
            unset($pos_singles2[0]);
            //если сформировалась пара со следующим числом=> ее нажо тоже удалить
            //проверка $slots = ["0" => "1 7 2 3 4 5 6 8", "1" => "3 7 2 4 6 5 8 1"];
            unset($pos_pares2[1]);
            unset($pos_pares[1]);
            //почистим навсякий и 2 выборку
            unset($pos_pares2[0]);
            unset($pos_pares[0]);
            //делаем этой паре +, если она еще не добавилась в оценивающий массив
            if($flag<>5) array_push($estMatPlus,substr($resp[1], 0,2));
            $flag = 5;

        }

        if(substr($resp[1], 2, 1)==8 or substr($resp[1], 2, 1)==6 or substr($resp[1], 2, 1)==7){
            $exclamationCount+=1;
            //Если стоит на 3 месте=> Мне нужно очистить основной массив пар от этого места и доп массив пар и приписать этим парам знак +
            unset($pos_singles2[0]);
            unset($pos_singles2[1]);
            unset($pos_singles2[2]);
            //если сформировалась пара со следующим числом=> ее нажо тоже удалить
            unset($pos_pares2[1]);
            unset($pos_pares[1]);
            unset($pos_pares2[0]);
            unset($pos_pares[0]);
            //тут я не буду ставить +, так как если сначала тоже было экстра-число=> его нужно убрать(да и пару тоже) И ВСЕ № ЧИСЛА БУДУТ СО ЗНАКОМ +
            if($flag<>5) array_push($estMatPlus,substr($resp[1], 0,2));//я проверяю флаг, так как эту пару я могла уже пушнуть в начале
            array_push($estMatPlus,substr($resp[1], 1,2));
            //а вот если на 2 позиции была пара из стремного числа+ другого, то эту пару надо переписать
            if(array_key_exists(2,$pos_pares2) or array_key_exists(2,$pos_pares)){
                unset($pos_pares2[2]); unset($pos_pares[2]);
                array_push($estMatx,substr($resp[1], 3,1));
                $flag=1;
            }
            if(array_key_exists(3,$pos_pares2) or array_key_exists(3,$pos_pares)){
                unset($pos_pares2[3]); unset($pos_pares[3]);
                if($flag<>1) array_push($estMatx,substr($resp[1], 3,1));
                if(!isset($pos_pares2[4]) and !isset($pos_pares[4])) {array_push($estMatEq,substr($resp[1], 4,1));}
                $flag=1;
            }

        }

        //именно в таком порядке, начиная с 8 позиции
        if(substr($resp[1], -1, 1)==1 or substr($resp[1], -1, 1)==2 or substr($resp[1], -1, 1)==3 or substr($resp[1], -1, 1)==4){
            //так как это последнее число, то смысла проверять пары неть и оно само станет - в конце, так что тут можно ничего не удалять и не присваивать
            $exclamationCount+=3;
            // единственное, что важно проверить - нет ли пары на 5 позиции
            // EX: 86745123 если есть пара на 5 позиции -> 51 - нужно обязательно ее оценить, иначе при дальнейшей проверке, если на 6 позиции окажется экстра-число - пара удалиться
            if(isset($pos_pares2[4])) {
                array_push($estMatEq,$pos_pares2[4]);
                unset($pos_pares2[4]);
            }
        }
        if(substr($resp[1], -2, 1)==1 or substr($resp[1], -2, 1)==2 or substr($resp[1], -2, 1)==3 or substr($resp[1], -2, 1)==4){
            $exclamationCount+=2;
            //удаляем 6 пару, даже если она где-то есть, здесь она нам не пригодится,
            // а единственного числа на 7 позиции в синглах существовать по природе не может
            unset($pos_pares2[6]);
            unset($pos_pares[6]);
            unset($pos_singles2[7]);
            //для дальнейшей проверки пары, которую мы сейчас внесем в оценивающий массив
            $flag=6;
            array_push($estMatMin,substr($resp[1], -2,2));
        }
        //проверка экстра-чисел не на своих местах, если 1 2 3 4 стоят на последних трех местах (6 7 или 8)
        if(substr($resp[1], -3, 1)==1 or substr($resp[1], -3, 1)==2 or substr($resp[1], -3, 1)==3 or substr($resp[1], -3, 1)==4){
            $exclamationCount+=1;
            array_push($estMatMin,substr($resp[1], -3,2));
            if($flag<>6)array_push($estMatMin,substr($resp[1], -2,2));
            // удаляем пары и числа на тех местах, на которых они могли стоять
            unset($pos_pares2[5]);
            unset($pos_pares[5]);
            unset($pos_pares2[6]);
            unset($pos_pares[6]);
            unset($pos_singles2[7]);
            unset($pos_singles2[5]);
            unset($pos_singles2[6]);
            //$flag=2;
        }

        //если 1 без пары=> сразу отправляется в оценивающий массив +, на 2 месте числа без пары не может быть
        if(isset($pos_singles2[0])) array_push($estMatPlus,$pos_singles2[0]);
        //если число без пары стоит на 3 месте, то оно обязательно будет x
        if(isset($pos_singles2[2])) array_push($estMatx,$pos_singles2[2]);
        // по алгоритму Люшера, если число на 4 месте без пары, а на 5 месте есть пара -> эти числа следует объединить в пару (нужно проверить только нет ли супер-чисел)
        if(isset($pos_singles2[3]) and isset($pos_pares[4]) and $flag===0) {
            $pos_pares[3]=$pos_singles2[3] . substr($pos_pares[4],0,1);
            unset($pos_singles2[3]);
        // если же на 4 месте нет пары, число нужно записать, как x и убрать из дальнейшей проверки
        } elseif(isset($pos_singles2[3])) {
            array_push($estMatx,$pos_singles2[3]);
            unset($pos_singles2[3]);
        }

        //число-одиночка на 5 позиции + пара на 6 месте=> нужно создать пару на 5 месте: 56
        if(isset($pos_singles2[4]) and isset($pos_pares[5])){
            array_push($estMatEq,$pos_singles2[4] . substr($pos_pares[5],0,1));
            unset($pos_singles2[4]);
        }
        if(isset($pos_singles2[4]) and isset($pos_pares2[5])) {
            array_push($estMatEq,$pos_singles2[4] . substr($pos_pares2[5] ,0,1));
            unset($pos_singles2[4]);
        }elseif(isset($pos_singles2[4])) {
            array_push($estMatEq,$pos_singles2[4]);
            unset($pos_singles2[4]);
        }

        //проверка 6 позиции с удалением пары на 4(если есть пара на 3), если у нас и так будет пара
        if(isset($pos_singles2[5]) and isset($pos_pares[3])) {
            array_push($estMatEq,substr($pos_pares[3],1,1) . $pos_singles2[5]);
            if(isset($pos_pares2[2]) or isset($pos_pares[2])) {unset($pos_pares[3]);unset($pos_pares2[3]);}
            unset($pos_singles2[5]);
        }

        //7 не может просто остаться единственным(либо с 6 либо с 8 объединится), 8 на всякий пушнем в оценку
        if(isset($pos_singles2[7]))array_push($estMatMin,$pos_singles2[7]);

        //Осталось проверить пары: на 1 месте -> сразу отправляются в оценку
        if(isset($pos_pares[0])) array_push($estMatPlus,$pos_pares[0]);
        if(isset($pos_pares2[0])) array_push($estMatPlus,$pos_pares2[0]);
        //удаляем пару на 2 месте, если есть пара на 1 и на 3 месте
        if(isset($pos_pares[0]) and isset($pos_pares[2])) unset($pos_pares[1]);

        //Если есть пара на 2 месте -> записываем ее в оценочный массив x
        if(isset($pos_pares[1])) array_push($estMatx,$pos_pares[1]);
        if(isset($pos_pares2[1])) array_push($estMatx,$pos_pares2[1]);


        /** Check two position whether they have a pair between or not
         * @param array $arr1  with pairs
         * @param array $arr2  with pairs
         * @param integer $position position to check neighbours
         * @return mixed
         */
        function check_neib($arr1, $arr2, $position){
            if(!isset($arr1[$position+1]) and !isset($arr2[$position+1])){
                if(isset($arr1[$position])){
                    if(isset($arr1[$position+2])) $arr1[$position+1]=substr($arr1[$position],1,1).substr($arr1[$position+2],0,1);
                    elseif(isset($arr2[$position+2])) $arr1[$position+1]=substr($arr1[$position],1,1).substr($arr2[$position+2],0,1);
                }
                elseif(isset($arr2[$position])){
                    if(isset($arr2[$position+2])) $arr1[$position+1]=substr($arr2[$position],1,1).substr($arr2[$position+2],0,1);
                    elseif(isset($arr1[$position+2])) $arr1[$position+1]=substr($arr2[$position],1,1).substr($arr1[$position+2],0,1);
                }
            }
            return $arr1;
        }

        //все усложняется с 3 позиции: если есть пара на 2 позиции и на 3 - нужно проверить
        //$flag = 1 - см в проверке на экстра-числа на 6 позиции
        if($flag!=1) {
            //если есть пара на 2 позиции и на 3 => нужно проверить, есть ли пара на 4 позиции, если ее нет, то
            if((isset($pos_pares[1]) or isset($pos_pares2[1])) and (isset($pos_pares[2]) or(isset($pos_pares[2])))){
                $pos_pares=check_neib($pos_pares,$pos_pares2,2);
            }
            // если есть пара на 4 и 6 позициях => нужно создать пару на 5 позиции (56)
            $pos_pares+=check_neib($pos_pares,$pos_pares2,3);
        }


        //а вот тут уже будет проверка на то, как же все-таки назначить =
        function check($arr1, $arr2)
        {
            if (isset($arr1[3]) or isset($arr2[3])) {
                if (isset($arr1[4]) or isset($arr2[4])) {
                    if (isset($arr1[5]) or isset($arr2[5])) {
                        if (isset($arr1[6]) or isset($arr2[6])) {
                            // (2) 3 4 5 6
                            unset($arr1[5]);
                            return $arr1;
                        }
                        // 2 3 4 5
                        if (isset($arr1[2])) unset($arr1[3]);
                        return $arr1;
                    }
                    // 1 2 3 4
                    if (isset($arr1[2]) and (isset($arr1[1]) or isset($arr2[1]))) unset($arr1[2]);
                }
            }
            return $arr1;
        }

        $pos_pares = check($pos_pares,$pos_pares2);
        $pos_pares2 = check($pos_pares2,$pos_pares);


        //Добавление пар в оценивающие массивы()
        if(isset($pos_pares[2]) and (isset($pos_pares[1]) or isset($pos_pares2[1]))) array_push($estMatEq,$pos_pares[2]);
        elseif(isset($pos_pares[2])) array_push($estMatx,$pos_pares[2]);
        if(isset($pos_pares2[2]) and (isset($pos_pares[1]) or isset($pos_pares2[1]))) array_push($estMatEq,$pos_pares2[2]); elseif(isset($pos_pares2[2])) array_push($estMatx,$pos_pares2[2]);


        if(isset($pos_pares[3])) array_push($estMatEq,$pos_pares[3]);if(isset($pos_pares2[3])) array_push($estMatEq,$pos_pares2[3]);
        if(isset($pos_pares[4])) array_push($estMatEq,$pos_pares[4]);if(isset($pos_pares2[4])) array_push($estMatEq,$pos_pares2[4]);

        if(isset($pos_pares[5])) array_push($estMatEq,$pos_pares[5]);if(isset($pos_pares2[5])) array_push($estMatEq,$pos_pares2[5]);
        if(isset($pos_pares[6])) array_push($estMatMin,$pos_pares[6]);if(isset($pos_pares2[6])) array_push($estMatMin,$pos_pares2[6]);

        //Очень важно! Проверить есть ли супер-числа + 1 и 8 место + какие числа нужно рассматривать как одиночки, а какие, как пары
        if(substr($resp[0], 0, 1)===substr($resp[1], -1, 1)) array_push($estMatPlusMin,substr($resp[0], 0, 1));
        if(substr($resp[1], 0, 1)===substr($resp[0], -1, 1)) array_push($estMatPlusMin,substr($resp[1], 0, 1));



        //+- функции Если только один + => конпануем его с минусами в конце
        //немного поигравшись в массивами, узнаем, сколько пар(чисел) в  оценивающих массивах + и -
        $arr2Pl = str_split(substr($resp[1],0,3));
        $arr2Min = str_split(substr($resp[1],-3,3));
        $a=array_unique(str_split(implode('', $estMatPlus)));
        $b=array_unique(str_split(implode('', $estMatMin)));

        $resPL = count(array_intersect($arr2Pl, $a));
        $resMin = count(array_intersect($arr2Min, $b));
        if($resPL>1) {
            array_push($estMatPlusMin, substr($resp[1],0,1). substr($resp[1],-1,1));
            array_push($estMatPlusMin, substr($resp[1],1,1). substr($resp[1],-1,1));
        }elseif ($resMin==1 and $resPL==1){
            array_push($estMatPlusMin, substr($resp[1],0,1). substr($resp[1],-1,1));
        }elseif($resMin>1 and $resPL==1){
            array_push($estMatPlusMin, substr($resp[1],0,1). substr($resp[1],-1,1));
            array_push($estMatPlusMin, substr($resp[1],0,1). substr($resp[1],-2,1));
        }


      // echo "<h1> Проверка на ФункцГр </h1> Функциональная группа + : "; print_r($estMatPlus); echo "</p> Проверка на ф-ции - :"; print_r($estMatMin); echo "</p> Проверка на ф-ции x :"; print_r($estMatx); echo "</p> Проверка на ф-ции =  :"; print_r($estMatEq);echo "</p> Проверка на ф-ции +-  :"; print_r($estMatPlusMin);

       include_once('LusherEstimArr.php');



        if($exclamationCount<3)$exclamLevlel="незначительная тревожность";
        elseif($exclamationCount<7)$exclamLevlel="<b style='color: rgba(255,173,42)'>эмоциональная напряженность</b>";
        elseif($exclamationCount<11)$exclamLevlel= "<b style='color: #78005a'>состояние дизадаптации</b>";
        else $exclamLevlel= "<b style='color: #b10202'> психологический и физический стресс</b>";
        $text = "<h4> Предварительный показатель тревожности: " . $exclamationCount . " из 12 – " .$exclamLevlel ." (нуждается в уточнении профессионалом)"; //эмоциональная напряженность.

        global $DB;
        // Добавление записи в базу данных с уникальным номером попытки, чтобы потом можно было сопоставить данные с пользователем
        $dbname = 'lusherr';


        $data = ['qubaid'=>$qubaid,'grplus'=> implode(',',$estMatPlus), 'grx'=> implode(',',$estMatx), 'greq'=>implode(',',$estMatEq), 'grmin'=>implode(',',$estMatMin),'grplusmin'=>implode(',',$estMatPlusMin),'firstans'=>$resp[0],'secondans'=>$resp[1],'exclamcount'=>$exclamationCount];
        if(!($DB->record_exists($dbname,array('qubaid'=>$qubaid))))   $DB->insert_record($dbname, $data);





        return $text;

    }
}
