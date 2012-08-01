<?php
require('admin.php');
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo 'ProfisCMS '.PC_VERSION; ?> - Links</title>
	<style type="text/css">
		#container {width:800px;margin:0 auto;}
	</style>
</head>
<body>
<div id="container">
	<ul>
		<li><a href="?action=find_duplicates">Rasti besidubliuojančias nuorodas</a></li>
		<li><a href="?action=routes_json">Dabartinių nuorodų JSON atsarginė kopija</a></li>
		<li><a href="?action=update_routes">Atnaujinti visas nuorodas</a></li>
	</ul>
	<br /><br />
	<?php
	switch(v($_GET['action'])) {
		case 'update_routes':
			if (v($_GET['confirm']) == 1) {
				$r = $db->query("SELECT name,id,route,ln FROM {$cfg['db']['prefix']}content");
				if ($r) {
					$routes = $r->fetchAll();
					$db->query("UPDATE {$cfg['db']['prefix']}content set route=''");
					echo '<h1>Routes</h1>';
					foreach ($routes as $route_data) {
						$new_route = Get_unique_route(Sanitize('route', $route_data['name']), $route_data['ln'], $route_data['id']);
						$r = $db->prepare("UPDATE {$cfg['db']['prefix']}content SET route=? WHERE id=?");
						$success = $r->execute(array($new_route, $route_data['id']));
						if ($success) {
							echo $route_data['id'].' <b>'.$route_data['route'].'</b> has been successfully updated to <b>'.$new_route.'</b><br />';
						}
						else {
							echo $route_data['id'].' <b style="color: red">'.$route_data['route'].'</b> has not been updated...<br />';
						}
					}
				}
			}
			else {
				?>
				Ar tikrai norite iš naujo sugeneruoti visas nuorodas?<br />
				Jei per adminą yra sudėtų nuorodų į vidinius puslapius ir po šio veiksmo tų puslapių nuorodos pasikeis - <b>jos nebeveiks</b>, todėl teks jas sudėti iš naujo.<br />
				(<a href="?action=routes_json">dabartinių nuorodų JSON atsarginė kopija</a>)<br />
				<a href="?action=update_routes&confirm=1"><b>Sutinku</b></a>
				<?php
			}
			break;
		case 'routes_json':
			echo '<h1>Routes backup in JSON</h1>';
			$r = $db->query("SELECT name,id,route FROM {$cfg['db']['prefix']}content");
			if (!$r) {
				echo 'Nepavyko nuskaityti.';
				break;
			}
			$routes = $r->fetchAll();
			echo json_encode($routes);
			break;
		case 'find_duplicates':
			if (!v($_GET['all'])) {
				echo '<a href="?action=find_duplicates&all=1">Rodyti visas</a>';
			}
			else echo '<a href="?action=find_duplicates">Rodyti tik tas, kurios dubliuojasi</a>';
			echo '<br />';
			$r = $db->query("SELECT route,count(id) total,group_concat(concat_ws('ÿ',pid,name)) pids FROM {$cfg['db']['prefix']}content GROUP BY route,ln,id");
			if (!$r) {
				echo 'Nepavyko nuskaityti nuorodų iš duomenų bazės';
				break;
			}
			if (!$r->rowCount()) {
				echo 'Besidubliuojančių nuorodų nerasta.';
				break;
			}
			echo '<table style="border-collapse:collapse;width:100%"><tr style="font-weight:bold;">'
			.'<td>Nuoroda</td><td>Naudojama</td><td>Puslapiai</td></tr>';
			while ($d = $r->fetch()) {
				if (!v($_GET['all']) && $d['total']==1) continue;
				echo '<tr style="border:1px solid #eee;"><td>'.$d['route'].'</td><td>'.$d['total'].'</td><td>';
				$pages = explode(',', $d['pids']);
				$pcnt = 0;
				foreach ($pages as $p) {
					$pcnt++;
					if ($pcnt>1) echo '<br />';
					$p = explode('ÿ', $p);
					echo '<a style="font-size:7pt" href="?action=get_page_info&pid='.v($p[0]).'">'.v($p[1]).'</a>';
				}
				echo '</td></tr>';
			}
			echo '</table>';
			break;
		case 'get_page_info':
			$pid = v($_GET['pid']);
			if (!$pid) {
				echo 'Puslapio ID nenurodytas.';
				break;
			}
			$site->Identify();
			$d = $page->Get_route_data($pid, true);
			echo 'Webpage ';
			if (count(v($d['path']))) foreach ($d['path'] as $p) {
				echo ' -> <b>'.$p['name'].'</b>';
			}
			echo '<hr />';
			print_pre($d);
			break;
		default:
			?>
			Pasirinkite veiksmą
			<?php
	}
	?>
</div>
</body>
</html>