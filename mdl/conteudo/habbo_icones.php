<? if($_GET['a'] == 1) {
	if($_POST['form'] == 'form') {
		$titulo = $core->clear($_POST['titulo']);
		$prosseguir = true;

		if(empty($titulo)) {
			$form_return .= aviso_red("Preencha todos os campos.");
			$prosseguir = false;
		}

		if($prosseguir) {
			$up_name = 'imagem';

			$up_gallery = $core->clear($_POST["gl-$up_name"]);
			$up_file = $_FILES["fl-$up_name"];
			$up_url = $core->clear($_POST["url-$up_name"]);

			$upload = new Upload($conn, $up_gallery, $up_file, $up_url, 'slide-', true);

			if(!$upload->erro) {
				$caminho_img = $upload->caminho;
			} else {
				$form_return .= aviso_red($upload->erro);
				$prosseguir = false;
			}
		}

		if($prosseguir) {
			$insert_data['titulo'] = $titulo;
			$insert_data['imagem'] = $caminho_img;
			$insert_data['autor'] = $autor;
			$insert_data['data'] = $timestamp;

			$insert = $sqlActions->insert($mdl_tabela, $insert_data);

			if($insert) {
				$core->logger("O usuário adicionou um novo ícone do Habbo", "acao");

				$form_return .= aviso_green("Sucesso!");
				foreach($_POST as $nome_campo => $valor){ $_POST[$nome_campo] = '';}
			} else {
				$form_return .= aviso_red("Ocorreu um erro ao executar a ação. Código de erro: {$sqlActions->error}");
			}
		}
	}
?>
<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?></div>

	<? echo $form_return;

	$form = new Form('form-submit', '');

	$form->createInput('Nome', 'text', 'titulo');
	$form->createUpload('Imagem', 'imagem');

	$form->generateForm();
	echo $form->form; ?>
</div>
<? } ?>

<? if($_GET['a'] == 2) {
	$id = $_GET['id'];

	if($_POST['form'] == 'form') {
		$titulo = $core->clear($_POST['titulo']);
		$prosseguir = true;

		if(empty($titulo)) {
			$form_return .= aviso_red("Preencha todos os campos.");
			$prosseguir = false;
		}

		if($prosseguir) {
			$up_name = 'imagem';

			$up_gallery = $core->clear($_POST["gl-$up_name"]);
			$up_file = $_FILES["fl-$up_name"];
			$up_url = $core->clear($_POST["url-$up_name"]);

			$_ex = $conn->prepare("SELECT * FROM $mdl_tabela WHERE id = ? LIMIT 1");
			$_ex->bindValue(1, $id);
			$_ex->execute();
			$ex = $_ex->fetch();

			$upload = new Upload($conn, $up_gallery, $up_file, $up_url, 'slide-', false, $ex['imagem']);

			if(!$upload->erro) {
				$caminho_img = $upload->caminho;
			} else {
				$form_return .= aviso_red($upload->erro);
				$prosseguir = false;
			}
		}

		if($prosseguir) {
			$update_data['titulo'] = $titulo;
			$update_data['imagem'] = $caminho_img;

			$where_data['id'] = $id;
			$update = $sqlActions->update($mdl_tabela, $update_data, $where_data);

			if($update) {
				$core->logger("O usuário editou o ícone do Habbo [#$id].", "acao");

				$form_return .= aviso_green("Sucesso!");
				foreach($_POST as $nome_campo => $valor){ $_POST[$nome_campo] = '';}
			} else {
				$form_return .= aviso_red("Ocorreu um erro ao executar a ação. Código de erro: {$sqlActions->error}");
			}
		}
	}

	$_ex = $conn->prepare("SELECT * FROM $mdl_tabela WHERE id = ? LIMIT 1");
	$_ex->bindValue(1, $id);
	$_ex->execute();
	$ex = $_ex->fetch();

	if(!$ex) {
		$script_js .= register404();
	}
?>
<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?></div>

	<button class="btn btn-danger" onclick="deletar(this, 1);" rel="?p=<?=$p;?>&a=3&id=<?=$id;?>">Inativar</button><br><br>

	<? echo $form_return;

	$form = new Form('form-submit', '');

	$form->createInput('Nome', 'text', 'titulo', $ex['titulo']);
	$form->createUpload('Imagem', 'imagem', $ex['imagem']);

	$form->generateForm();
	echo $form->form; ?>
</div>
<? } ?>

<? if($_GET['a'] == 339955) {
	$id = $_GET['id'];

	$ids = explode(',', $id);
	$ids = array_filter($ids);

	if(count($ids) > 0) {
		$delete = $conn->prepare("DELETE FROM $mdl_tabela WHERE id = ? LIMIT 1");
		$delete->bindParam(1, $id_atual);

		foreach ($ids as $id_atual) {
			$delete->execute();

			$core->logger("O usuário deletou o registro [#$id_atual - $mdl_tabela]", "acao");
		}
	} else {
		$delete_where['id'] = $id;
		$delete = $sqlActions->delete($mdl_tabela, $delete_where);

		$core->logger("O usuário deletou o registro [#$id_atual - $mdl_tabela]", "acao");
	}
} ?>

<? if($_GET['a'] == 3) {
	$id = $_GET['id'];

	$ids = explode(',', $id);
	$ids = array_filter($ids);

	if(count($ids) > 0) {
		$delete = $conn->prepare("UPDATE $mdl_tabela SET status = 'inativo' WHERE id = ? LIMIT 1");
		$delete->bindParam(1, $id_atual);

		foreach ($ids as $id_atual) {
			$delete->execute();

			$core->logger("O usuário deletou o ícone do Habbo [#$id_atual]", "acao");
		}
	} else {
		$delete_where['id'] = $id;
		$delete = $conn->prepare("UPDATE $mdl_tabela SET status = 'inativo' WHERE id = ? LIMIT 1");
		$delete->bindValue(1, $id);
		$delete->execute();

		$core->logger("O usuário deletou o ícone do Habbo [#$id]", "acao");
	}
} ?>

<? if($_GET['a'] == 4) {
	$id = $_GET['id'];

	$reset = $conn->query("ALTER TABLE $mdl_tabela AUTO_INCREMENT = 1;");
	$core->logger("O usuário resetou o AI de $mdl_tabela", "acao");

	echo "<script>location.replace('?p=$p');</script>";
} ?>

<? if($_GET['a'] == 9) {
	$id = $_GET['id'];

	$ativar = $conn->prepare("UPDATE $mdl_tabela SET status = 'ativo' WHERE id = ?");
	$ativar->bindValue(1, $id);
	$ativar->execute();

	$core->logger("O usuário ativou o registro [#$id - $mdl_tabela]", "acao");
} ?>

<? if($_GET['a'] == '') { ?>
<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?></div>
	<a href="?p=<?=$_GET['p'];?>&a=1"><button class="btn btn-primary">Adicionar</button></a>
	<? if($core->allAccess()) { ?><a href="?p=<?=$_GET['p'];?>&a=4"><button class="btn btn-danger">Resetar AI [DEV]</button></a><? } ?>
	<button class="btn btn-info" onclick="searchShow();">Pesquisar</button>
	<? if($_POST['search'] == 'search') { ?><a href="?p=<?=$_GET['p'];?>"><button class="btn btn-warning">Limpar busca</button></a><? } ?>
	<br><br>

	<?php

	$search = getSearchForm();
	echo $search;

	?>

	<?
	$table = new Table('', true, $core->allAccess());
	$table->head(array('#', 'Nome', 'Autor', 'Data', 'Ações'));

	$table->startBody();

	$limite = 15;
	$pagina = $_GET['pag'];
	((!$pagina)) ? $pagina = 1 : '';
	$inicio = ($pagina * $limite) - $limite;

	$query = "$mdl_tabela ORDER BY id DESC";

	if($_POST['search'] == 'search') {
		$busca = $core->clear($_POST['busca']);
		$limite = 5000;

		$campo = "nick";

		$query = "$mdl_tabela WHERE $campo LIKE ? ORDER BY id DESC";
		$sql = $conn->prepare("SELECT * FROM $query LIMIT $inicio,$limite");
		$sql->bindValue(1, '%'.$busca.'%');
		$sql->execute();

		$_rows = $conn->prepare("SELECT count(id) FROM $query");
		$_rows->bindValue(1, '%'.$busca.'%');
		$_rows->execute();
		$total_registros = $_rows->fetchColumn();

		echo '<div class="searching">Pesquisando por: <b>'.$busca.'</b></div>';
	} else {
		$sql = $conn->query("SELECT * FROM $query LIMIT $inicio,$limite");
		$total_registros = $core->getRows("SELECT * FROM $query");
	}

	while($sql2 = $sql->fetch()) {
		$table->insertBody(array($sql2['id'], $core->clear($sql2['titulo']), $core->clear($sql2['autor']), $core->clear(date('d/m/Y H:i', $sql2['data'])), 'actions'), $sql2['status']);
	}

	$table->closeTable();
	echo $table->table;

	if($total_registros == 0) {
		echo aviso_red("Nenhum registro encontrado.");
	} else {
		echo '<ul class="pagination">';

		$total_paginas = ceil($total_registros / $limite);

		$links_laterais = ceil($limite / 2);

		$inicio = $pagina - $links_laterais;
		$limite = $pagina + $links_laterais;

		for ($i = $inicio; $i <= $limite; $i++){
			if ($i == $pagina) {
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
			} else {
				if ($i >= 1 && $i <= $total_paginas){
					$link = '?' . $_SERVER["QUERY_STRING"];
					$link = preg_replace('/(\\?|&)pag=.*?(&|$)/','',$link);
					echo '<li><a href="'.$link.'&pag='.$i.'">'.$i.'</a></li>';
				}
			}
		}

		echo '</ul>';
	} ?>

	<?php

	if($total_registros > 0) {
		$marked = getMarkedSelect($p, 3);
		echo $marked;
	}

	?>
</div>
<? } ?>