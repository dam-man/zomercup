<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\text;

class ResetPasswordCommand extends Command
{
	protected $signature = 'reset:password';

	protected $description = 'Command description';

	public function handle(): int
	{
		$email = text(
			label: 'Welk email moet gereset worden?',
			required: true
		);

		$user = User::where('email', $email)->first();

		if(!$user)
		{
			$this->error('Onbekende gebruiker!');

			return Command::SUCCESS;
		}

		$password = text(
			label: 'Wat is het nieuwe wachtwoord?',
			required: true
		);

		$user->update([
			'password' => Hash::make($password),
		]);

		return Command::SUCCESS;
	}
}
