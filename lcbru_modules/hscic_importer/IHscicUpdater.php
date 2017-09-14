<?php
interface IHscicUpdater
{
    public function update(array $values);
    public function complete();
}