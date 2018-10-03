<?php

use Illuminate\Database\Seeder;

class OAuthClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('oauth_clients')->insert(array(
			'client_id' => "favoclient",
			'client_secret' => "favopass",
			'redirect_uri' => "http://fake/",
		));
    }
}
