<?php

class ExtractorModel extends NObject{
	static function getFluent(){
		return dibi::select("email")
				->from("link")
				->leftJoin("email")->using("(domain)")
				->where('email.checked = 1');
	}
}