<?php
function format_date($date,$to = 'd/m/Y',$from = 'Y-m-d')
{
	return \Carbon\Carbon::createFromFormat($from,$date)->format($to);
}

function minutosToHoras($min)
 { //obtener segundos
 	$minutes = $min;
 	$hours =   sprintf('%02d',intdiv($minutes, 60)) .':'. ( sprintf('%02d',$minutes % 60));
 	return $hours;
	$sec = $min * 60;

	// dias es la division de n segs entre 86400 segundos que representa un dia

	$dias = floor($sec / 86400);

	// mod_hora es el sobrante, en horas, de la division de días;

	$mod_hora = $sec % 86400;

	// hora es la division entre el sobrante de horas y 3600 segundos que representa una hora;

	$horas = floor($mod_hora / 3600);

	// mod_minuto es el sobrante, en minutos, de la division de horas;

	$mod_minuto = $mod_hora % 3600;

	// minuto es la division entre el sobrante y 60 segundos que representa un minuto;

	$minutos = floor($mod_minuto / 60);
	if ($horas <= 0)
	{
		$text = '00:'.sprintf('%02d', $minutos);
	}
	elseif ($dias <= 0)
	{
		$text = sprintf('%02d', $horas) . ":" . sprintf('%02d', $minutos);
	}
 	else
	{
		$text = sprintf('%02d', $horas) . ":" . sprintf('%02d', $minutos);
	}

	return $text;
	
	
}