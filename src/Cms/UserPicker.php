<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;

class UserPicker extends Picker
{
	/**
	 * Extends the basic defaults
	 */
	public function defaults(): array
	{
		return [
			...parent::defaults(),
			'text' => '{{ user.username }}'
		];
	}

	/**
	 * Search all users for the picker
	 *
	 * @throws \Infinri\Exception\InvalidArgumentException
	 */
	public function items(): Users|null
	{
		$model = $this->options['model'];

		// find the right default query
		$query = match (true) {
			empty($this->options['query']) === false
				=> $this->options['query'],
			$model instanceof User
				=> 'user.siblings',
			default
			=> 'kirby.users'
		};

		// fetch all users for the picker
		$users = $model->query($query);

		// catch invalid data
		if ($users instanceof Users === false) {
			throw new InvalidArgumentException(
				message: 'Your query must return a set of users'
			);
		}

		// filter protected and hidden users
		$users = $users->filter('isListable', true);

		// search & sort
		$users = $this->search($users)->sort('username', 'asc');

		// paginate
		return $this->paginate($users);
	}
}
