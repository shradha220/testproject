<?php

use Illuminate\Database\Seeder;

class OAuthUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('oauth_users')->insert(array(
			'username' => "favoservices",
			'password' => "favo123",
			'first_name' => "favo",
			'last_name' => "services",
		));
    }
}
