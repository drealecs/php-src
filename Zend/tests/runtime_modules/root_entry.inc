<?php
class RootEntryClass {
    public function context(): RuntimeModule { return RuntimeModule::current(); }
}
return new RootEntryClass();
