# PHP Template Engine

php template engine base on php 5.4+.

For a more current PHP 5 implementation of the same ideas, check out phpsavant.com.

Example
'''
<?php
$path = '/path/to/templates/';

tpl = new Template($path);
$tpl->set('title', 'Goods List');
$body = new Template($path);
$body->set('goods', fetch_goodslist());
$tpl->set('content', $body->fetch('goodslist.tpl'));
echo $tpl->fetch('index.tpl');
?>
'''
And it can be used with the following templates.

<!-- goodslist.tpl -->
''''
<table>
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Price</th>
        <th>IsVIP</th>
    </tr>
    <? foreach($goodslist as $item): ?>
    <tr>
        <td align="center"><?=$item['id'];?></td>
        <td><a href="goods/<?=$item['id'];?>"><?=$item['name'];?></a></td>
         <td><?=$item['price'];?></td>
        <td align="center"><?=($item['vip'] ? 'VIP' : '');?></td>
    </tr>
    <? endforeach; ?>
</table>
''''
<!-- index.tpl -->
''''
<html>
    <head>
        <title><?=$title;?></title>
    </head>
    <body>
        <h2><?=$title;?></h2>
        <?=$content;?>
    </body>
</html>
''''
