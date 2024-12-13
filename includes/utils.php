<?php
	function generateToken($length = 32) {
	    // Tạo chuỗi ngẫu nhiên bằng cách sử dụng random_bytes (mạnh mẽ hơn rand())
	    $bytes = random_bytes($length); // Tạo chuỗi ngẫu nhiên với chiều dài là 32 byte
	    return bin2hex($bytes);         // Chuyển chuỗi ngẫu nhiên thành chuỗi hex (64 ký tự)
	}


    class User {
        public function __construct($u, $p, $d) {
            $this->username = $u;
            $this->password = $p;
            $this->description = $d;
        }
    }

    class Class_Post {
        public function __construct($c, $n, $p, $e, $d) {
            $this->code = $c;
            $this->name = $n;
            $this->professor = $p;
            $this->ects = $e;
            $this->description = $d;
        }
    }

    class Log {
        public function __construct($f, $m) {
            $this->f = $f;
            $this->m = $m;
        }
        
        public function __destruct() {
            file_put_contents($this->f, $this->m, FILE_APPEND);
        }
    }
?>

    class User {
        public function __construct($u, $p, $d) {
            $this->username = $u;
            $this->password = $p;
            $this->description = $d;
        }
    }

    class Class_Post {
        public function __construct($c, $n, $p, $e, $d) {
            $this->code = $c;
            $this->name = $n;
            $this->professor = $p;
            $this->ects = $e;
            $this->description = $d;
        }
    }

    class Log {
        public function __construct($f, $m) {
            $this->f = $f;
            $this->m = $m;
        }
        
        public function __destruct() {
            file_put_contents($this->f, $this->m, FILE_APPEND);
        }
    }
?>
