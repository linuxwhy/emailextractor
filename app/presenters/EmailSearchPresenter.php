<?php

/**
 * Description of EmailSearchPresenter
 *
 * @author oaki
 */
class EmailSearchPresenter extends BasePresenter {

	function actionSearch(Nette\Forms\Form $f){
		
		$this->template->sql = array();
		
		$values = $f->getValues();
		$query = trim($values['query']);
		$spojka = $values->spojka;
		
		$words = explode(" ", $query);
		
		if($values->search_zlatestranky){
			$array_to_sql = array();

			foreach($words as $w){
				$var = array('[email] LIKE %s','%'.$w.'%','
					OR [title] LIKE %s','%'.$w.'%','
					OR [keywords] LIKE %s','%'.$w.'%','
					OR [description] LIKE %s','%'.$w.'%','
					OR [link] LIKE %s','%'.$w.'%','
					OR [info] LIKE %s','%'.$w.'%','
				');

				array_push($array_to_sql, $var);
	//			array_push($array_to_sql, 'OR');
			}

	//		array_pop($array_to_sql);
	//		array_push($array_to_sql, ')');

			$list = ZlatestrankyModel::getFluent()->select('email')->where('%'.$spojka,
				$array_to_sql
			)->fetchAssoc('email');
			$this->template->sql[] = dibi::$sql;
		}
		
		
		if($values->search_web_pages){
			$array_to_sql = array();

			foreach($words as $w){
				$var = array('[email] LIKE %s','%'.$w.'%','
					OR [title] LIKE %s','%'.$w.'%','
					OR [keywords] LIKE %s','%'.$w.'%','
					OR [description] LIKE %s','%'.$w.'%','
					OR [link] LIKE %s','%'.$w.'%','				
				');

				array_push($array_to_sql, $var);
			}

			$list2 = ExtractorModel::getFluent()->where('%'.$spojka,
				$array_to_sql
			)->fetchAssoc('email');
			$this->template->sql[] = dibi::$sql;
		}
		
		
		$this->template->list = array();
		
		if(isset($list))
			$this->template->list += $list;
		
		if(isset($list2))
			$this->template->list += $list2;
		
	}

	public function actionDefault() {
		
	}

	public function renderDefault() {
		
	}
	
	function createComponentSearchForm($name) {
		$f = new Nette\Application\UI\Form;
		$f->addText('query', 'Hladany text');
		$f->addSubmit('btn', 'Vyhladaj');
		$f->addSelect('spojka', 'Spojka medzi slovami', array('or'=>'or', 'and'=>'and'));
		$f->addCheckbox('search_zlatestranky', 'Hladat na Zlatych strankach');
		$f->addCheckbox('search_web_pages', 'Hladat na Webovych strankach');
		$f->onSubmit[] = array($this, 'actionSearch');
		
		$f->setDefaults( array('spojka'=>"or"));
		return $f;
	}

}