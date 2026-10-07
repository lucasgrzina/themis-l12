<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::truncate();
        DB::statement('TRUNCATE TABLE model_has_roles');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
  
        $items = [
        [1,'Wavecom','wavecom','wavecom',1,1769],
        [31,'Analia','analia','analia',1,472],
        [104,'Adriana Valeiras','adriana','l11109c',1,472],
        [470,'Daniela Spinelli','daniela','daniela',2,473],
        [471,'Carlos Casabella','carlos','carlos',2,528],
        [487,'Miguel Romero','miguel','miguel',2,919],
        [488,'Nieto Natalia','natalia','natalia',2,527],
        [526,'Eugenia Faraldo','eugenia','euge84',2,846],
        [527,'Lucia Soledad Sassi','soledad','soledad',2,1718],
        [528,'rafael','rafael','rafael',2,1016],
        [529,'Maria Ines Rodriguez Maiorano','ines','ines79',2,1758],
        [530,'Sandra Marotta','sandra','malena',1,1769],
        [531,'Maria Jose Solimanto','MJSolimanto','solimanto',2,2076],
        [532,'Julieta Martinez','Julieta','martinez',2,2764],
        [533,'Natalia Amarilla','NAmarilla','amarilla',2,2759],
        [534,'Sol Garcia Ricca','Sol','ricca',2,2954],
        [535,'MónicaCassano','Mónica','monica',3,NULL],
        [536,'Gladys Bobinac','Gladys','123456',2,NULL],
        [537,'Emilce Guisado','Emilce','memi',2,NULL],
        [538,'Norma Castellano','norma','norma',3,NULL],
        [539,'Lorena','lorena','lorena',2,3776],
        [540,'Jessica Casas','jessica','jessica',2,3825],
        [541,'Mariana Cecilia Cabrera','marianacabrera','Mariana',2,4254],
        [542,'Lucrecia Bochini','lucrecia','9614',2,6009],
        [543,'Stella Larmand','stella','stella',2,NULL],
        [544,'Johanna','Johanna','Johanna',2,NULL],
        [545,'Silvina','Silvina','Silvina',2,NULL],
        [546,'Romina Schilling','romina','romina',2,NULL],
        [547,'Ivana Mengarelli','imengarelli','2966',2,6312],
        [548,'Graciela Carrazco','Graciela','xxxxxx',2,6448],
        [549,'mariana','mariana','mariana',2,NULL],
        [550,'Brenda Malmsten','brenda','111111',1,NULL],
        [551,'Belmonte Mariana','Belmonte Mariana','mariana',2,NULL],
        [552,'Belen Jungblut','belen','belen',1,NULL],
        [553,'CARLA AVILA','CARLA','111111',1,NULL],
        [554,'LAURA ORTEMBERG','LAURA','111111',2,NULL],
        [555,'DARIO CAMPOS','DARIO','111111',1,NULL],
        [556,'Florencia Dziedzic','Florencia','flor01',2,NULL],
        [557,'Maria Dolores Piazza','Dolores','111111',2,NULL],
        [558,'GABRIELA BUFFA','GABRIELA','gabriela',2,7365],
        [559,'Florencia Di Palma','Maria Florencia','florflor',1,NULL],
        [560,'Mariana','mariana1','estudio',2,7810],
        [561,'Florencia De la Fuente','fdelafuente','Flor2906',2,NULL],
        [562,'Martina Diaz Hoyos','Martina','Diaz01',1,NULL],
        [563,'MARIA EUGENIA GONZALEZ','M EUGENIA','me2015',2,7892],
        [564,'andrea camacho','ANDREA','ANDREA01',1,NULL],
        [565,'Guillermina Vatteone','GVatteone','guille2016',2,NULL],
        [566,'GABY GIACOBINI','GABY GIACOBINI','gaby2016',2,NULL],
        [567,'ANALIA BARRIENTOS','analia2','analia',1,NULL],
        [568,'MF Echavarria','MF Echavarria','maria2017',2,NULL],
        [569,'Angela Bruni','angela','angela',2,NULL],
        [570,'Agustina Ithuralde','agustina','agus18',2,NULL],
        [571,'Josefina Beuamarie','jbeaumarie','josefina',2,NULL],
        [572,'Julieta Cibeira','jcibeira','jul-17',2,NULL],
        [573,'Cintya Gross','cgross','cinty2017',1,NULL],
        [574,'Tatiana Queipo','tatiana','tatiana',1,NULL],
        [575,'Tatiana','tatiana2','tatiana',1,NULL],
        [576,'Josefina Beaumarie','josefina','joseina',2,8316],
        [577,'Aldana Denise Raab','aldana','aldana',2,NULL],
        [578,'Agustina Drago Yawny','adrago','adrago',2,NULL],

        ];

        foreach ($items as $item) {
            $user = User::create([
                'id' => $item[0],
                'name' => $item[1],
                'email' => $item[0].'@nomail.com',
                'username' => \Illuminate\Support\Str::slug($item[2]),
                'password' => Hash::make( $item[3] ),
                'responsable' => ($item[5] !== NULL)
            ]);

            $role = (Role::find($item[4]))->name;
            $user->assignRole($role);
        }

    }
}
