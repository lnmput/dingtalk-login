<?php

Route::get('login/dingtalk', 'Lnmput\Dingtalk\DingtalkLogin@login')->name('login.dingtalk');;

Route::get('login/dingtalk/callback', 'Lnmput\Dingtalk\DingtalkLogin@callback');

