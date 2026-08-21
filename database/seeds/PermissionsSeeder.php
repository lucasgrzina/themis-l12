<?php

use App\Models\AccionesControladas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acciones = [
        	'C',
        	'R',
        	'U',
        	'D'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $accionesControladas = AccionesControladas::pluck('nombre'); 

        foreach ($accionesControladas as $ac) {
        	foreach ($acciones as $a) {
        		Permission::create([
        			'name' => \Illuminate\Support\Str::slug($ac).':'.$a,
        			'display_name' => ucfirst($a).' '.$ac
        		]);
        	}
        }
    }
}
