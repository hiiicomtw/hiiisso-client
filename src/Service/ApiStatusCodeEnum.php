<?php

namespace App\Services\Api;


class ApiStatusCodeEnum
{
    /*=========== SUCCESS =============*/
    const SUCCESS = '000000';

    /*=========== EXCEPTION =============*/
    const OTHER_EXCEPTION = '000001';
    const DATA_NOT_FOUND = '000002';
    const UNAUTHORIZED = '000003';
    const PERMISSION_DENIED = '000004';
    const GET_TOKEN_EXCEPTION = '000005';
    const HAS_BEEN_CREATED = '000006';
    const SN_REPEATED = '000007';
    const INVALID_REQUEST = '000008';
    const OAUTH_EXCEPTION = '000009';
    const OAUTH_RESULT_EXCEPTION = '000010';
    const HTTP_RESPONSE_EXCEPTION = '000011';
    const FAILED_VALIDATION = '000012';
    const VALID_EXCEPTION = '000013';
    const TOKEN_MISMATCH = '000014';
    const SEND_MAIL_FAILED = '000015';
    const NOT_ACTIVE_EXCEPTION = '000016';
    const EXPIRED_KEY_EXCEPTION = '000017';
    const NOT_FOUND_PRE_ORDER = '000018';
    const ORDER_STORE_FAIL = '000019';
    const SEND_SMS_FAILED = '000020';
    const NOTHING_TO_UPDATE = '000021';


    public static function getStatusMessageList()
    {
        return [
            self::SUCCESS => '成功',
            self::OTHER_EXCEPTION => '未知錯誤(例外)',
            self::DATA_NOT_FOUND => '找不到資料',
            self::UNAUTHORIZED => '尚未登入',
            self::PERMISSION_DENIED => '權限不足',
            self::GET_TOKEN_EXCEPTION => '取得token失敗',
            self::HAS_BEEN_CREATED => '已被新增',
            self::SN_REPEATED => '編號已被使用',
            self::INVALID_REQUEST => '請求違例',
            self::HTTP_RESPONSE_EXCEPTION => 'Http回應錯誤',
            self::FAILED_VALIDATION => '驗證輸入錯誤',
            self::VALID_EXCEPTION => '有效例外',
            self::TOKEN_MISMATCH => '閒置時間過長，網頁過期。',
            self::SEND_MAIL_FAILED => 'Email寄送失敗',
            self::NOT_ACTIVE_EXCEPTION => '狀態未開通',
            self::EXPIRED_KEY_EXCEPTION => '驗證失敗',
            self::NOT_FOUND_PRE_ORDER => '找不到父訂單',
            self::ORDER_STORE_FAIL => '新增訂單失敗',
            self::SEND_SMS_FAILED => '簡訊寄送失敗',
            self::NOTHING_TO_UPDATE => '無資料更新',
        ];
    }

    public static function getStatusMessage($code = '000000')
    {
        $list = self::getStatusMessageList();
        return $list[$code];
    }
}