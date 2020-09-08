<?php

namespace app\api\controller\live;

Final class AppCore {
    
    public static function appException($e) {
        die(json_encode(['code' => $e->getCode() ?: 99, 'msg' => $e->getMessage() ] ) );
    }
}
