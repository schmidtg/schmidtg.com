<?php

class Home_Controller extends Base_Controller {

	public function action_index()
	{
		return View::make('home.index');
	}

	public function action_about()
	{
		return View::make('home.about');
	}

	public function action_work()
	{
		return View::make('home.work');
	}

	public function action_contact()
	{
		return View::make('home.contact');
	}

}
