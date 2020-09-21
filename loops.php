<?php 
$arr = [1,2,3,4,5,6,7,8,9,10];

echo "this loop echoes the value of each num in the array\n\n";
foreach($arr as $num){
echo "$num\n";
}

echo "\n";

echo "this loop echoes only the value of even nums\n\n";
foreach($arr as $num){
if($num % 2 == 0){
echo "$num \n";
}
}

echo "\n";

echo "This is achieved by utilizing modulo 2 to check that the remainder is 0\nthus ensuring the number is cleanly divisible by two and thus even\n";
?>
