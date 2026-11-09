<?php
var_dump(get_defined_vars(), isset($this));
$entryLocal = 'private';
class EntryService {
    public function context(): RuntimeModule { return RuntimeModule::current(); }
}
return new EntryService();
