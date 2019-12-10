<?php
if( !defined( 'DATALIFEENGINE' ) ) {
    header( "HTTP/1.1 403 Forbidden" );
    header ( 'Location: ../../' );
    die( "Hacking attempt!" );
}

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$api_name = 'users';
$possibleData = array(
	array(
		'name' => 'email',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'password',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'name',
		'type' => 'string',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'user_id',
		'type' => 'integer',
		'required' => false,
		'post' => false,
	),
	array(
		'name' => 'news_num',
		'type' => 'interger',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'comm_num',
		'type' => 'interger',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'user_group',
		'type' => 'integer',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'lastdate',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'reg_date',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'banned',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'allow_mail',
		'type' => 'boolean',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'info',
		'type' => 'string',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'signature',
		'type' => 'string',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'foto',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'fullname',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'land',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'favorites',
		'type' => 'string',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'pm_all',
		'type' => 'integer',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'pm_unread',
		'type' => 'integer',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'xfields',
		'type' => 'string',
		'required' => true,
		'post' => true,
	),
	array(
		'name' => 'allowed_ip',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'hash',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'logged_ip',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'restricted',
		'type' => 'boolean',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'restricted_days',
		'type' => 'integer',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'restricted_date',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'timezone',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'news_subscribe',
		'type' => 'boolean',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'comments_reply_subscribe',
		'type' => 'boolean',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'twofactor_auth',
		'type' => 'boolean',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'cat_add',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
	array(
		'name' => 'cat_allow_addnews',
		'type' => 'string',
		'required' => false,
		'post' => true,
	),
);

// possibleData
// $possibleData[] = array(
//                  'name' => 'DBColumn name',
//                  'type' => "Type of value",  // integer, string, boolean, double
//                  'required' => true/false,   // Обязательное поле?
//                  'post' => true/false,       // Разрешить использовать при добавлении или редактуре?
// );
// possibleData Add

$app->group('/' . $api_name, function ( ) use ( $connect, $api_name, $possibleData ) {
	$header = array();
	$access = array(
		'full' => false,
		'can_read' => false,
		'can_write' => false,
		'can_delete' => false,
	);

	$this->get('[/]', function (Request $request, Response $response, Array $args) use ($possibleData, $api_name, $connect, $header, $access ) {
		foreach ( $request->getHeaders() as $name => $value ) {
			$name = strtolower(str_replace('HTTP_', '', $name));
			$header[$name] = $value[0];
		}

		$checkAccess = checkAPI($header['x_api_key'], $api_name);
		if (isset($checkAccess['error'])) return $response->withStatus(400)->getBody()->write(json_encode(array('error' => $checkAccess['error'])));

		$access['full'] = $checkAccess['admin'];
		$access['can_read'] = $checkAccess['read'];

		if ($access['full'] || $access['can_read']) {
			$orderBy = $header['orderby'] ?: 'id';
			$sort = $header['sort'] ?: 'DESC';
			$limit = $header['limit'] ? 'LIMIT '.(int)$header['limit'] : '';

			$possibleParams = '';

			foreach ( $header as $data => $value) {
				$keyData = array_search($data, array_column($possibleData, 'name'));

				if ($keyData !== false) {
					$postData = $possibleData[$keyData];
					if ( strlen( $possibleParams ) === 0 ) $possibleParams .= " WHERE {$data}" . getComparer( $header[$data], $postData['type'] );
					else $possibleParams .= " AND {$data}" . getComparer( $header[$data], $postData['type'] );
				}
			}

			$sql = 'SELECT * FROM '. USERPREFIX . "_{$api_name} {$possibleParams} ORDER by {$orderBy} {$sort} {$limit}";

			$getData = new CacheSystem($api_name, $sql);
			if (empty($getData->get())) {
				$data = $connect->query($sql);
				$getData->setData(json_encode($data));
				$getData->create();
			} else {
				$data = json_decode($getData->get(), true);
			}

			$response->withStatus( 200 )->getBody()->write( json_encode( $data ) );

		} else {

			$response->withStatus(400)->getBody()->write(json_encode(array('error' => 'У вас нет прав на просмотр данных!')));

		}

		return $response->withHeader('Content-type', 'application/json; charset=UTF-8');
	});

	$this->post('[/]', function (Request $request, Response $response, Array $args) use ($possibleData, $api_name, $connect, $header, $access ) {
		foreach ( $request->getHeaders() as $name => $value ) {
			$name = strtolower(str_replace('HTTP_', '', $name));
			$header[$name] = $value[0];
		}

		$body = array();
		foreach( $request->getParsedBody() as $name => $value ) $body[$name] = $value;


		if (empty($body))
			return $response->withStatus(400)->getBody()->write(json_encode(array('error' => 'Требуемая информация пуста: Заполните POST-форму и попробуйте снова!')));

		$checkAccess = checkAPI($header['x_api_key'], $api_name);
		if (isset($checkAccess['error'])) return $response->withStatus(400)->getBody()->write(json_encode(array('error' => $checkAccess['error'])));

		$access['full'] = $checkAccess['admin'];
		$access['can_write'] = $checkAccess['write'];

		if ($access['full'] || $access['can_write']) {

			$names = array();
			$values = array();

			foreach ( $body as $name => $value ) {
				$keyNum = array_search($name, array_column($possibleData, 'name'));

				if ($keyNum !== false) {
					$keyData = $possibleData[$keyNum];

					if ( $keyData['post'] === false) continue;

					if ( $keyData['required'] && empty($value))
						return $response->withStatus(400)->getBody()->write(json_encode(array('error' => "Требуемая информация отсутствует: {$name}!")));

					$names[] = $name;
					$values[] = defType($value, $keyData['type']);

				}
			}

			$names = implode(', ', $names);
			$values = implode(', ', $values);

			$sql = "INSERT INTO " . USERPREFIX . "_{$api_name} ({$names}) VALUES ({$values})";
			$connect->query( $sql );

			// Почему я не люблю MySQL? Потому что нельзя вернуть данные сразу после добавления в базу данных!
			// All Heil PostgreSQL! `INSERT INTO xxx (yyy) VALUES (zzz) RETURNING *`! Вот так просто!
			// Но нет, в MySQL нужно строить такой костыль!!!
			$lastID = $connect->lastInsertId();
			$sql = "SELECT * FROM " . PREFIX . "_{$api_name} WHERE id = :id";
			$data = $connect->row($sql, array('id' => $lastID));

			$cache = new CacheSystem($api_name, $sql);
			$cache->clear($api_name);
			$cache->setData(json_encode($data));

			$response->withStatus( 200 )->getBody()->write( json_encode( $data ) );

		} else {

			$response->withStatus(400)->getBody()->write(json_encode(array('error' => 'У вас нет прав на добавление новых данных!')));

		}

		return $response->withHeader('Content-type', 'application/json; charset=UTF-8');
	});

	$this->put('/{id:[0-9]+}[/]', function (Request $request, Response $response, Array $args) use ($possibleData, $api_name, $connect, $header, $access ) {
		foreach ( $request->getHeaders() as $name => $value ) {
			$name = strtolower(str_replace('HTTP_', '', $name));
			$header[$name] = $value[0];
		}

		$body = array();
		foreach( $request->getParsedBody() as $name => $value ) $body[$name] = $value;

		if (empty($body))
			return $response->withStatus(400)->getBody()->write(json_encode(array('error' => 'Требуемая информация пуста: Заполните POST-форму и попробуйте снова!')));

		$checkAccess = checkAPI($header['x_api_key'], $api_name);
		if (isset($checkAccess['error'])) return $response->withStatus(400)->getBody()->write(json_encode(array('error' => $checkAccess['error'])));

		$access['full'] = $checkAccess['admin'];
		$access['can_write'] = $checkAccess['write'];

		if ($access['full'] || $access['can_write']) {

			$id = $args['id'];
			if (!(int)$id)
				return $response->withStatus(400)->getBody()->write(json_encode(array('error' => 'Требуемая информация отсутствует: ID!')));
			$values = array();

			foreach ( $body as $name => $value ) {
				if ( defType($value) !== null && in_array($name, $possibleData)) {
					$values[] = "{$name} = " . defType($value);
				}
			}
			$values = implode(', ', $values);

			$sql = 'UPDATE '. USERPREFIX . "_{$api_name} SET {$values} WHERE id = :id";
			$connect->query( $sql, array('id' => $id) );

			$sql = 'SELECT * FROM '. USERPREFIX . "_{$api_name} WHERE id = :id";
			$data = $connect->row($sql, array('id' => $id));

			$cache = new CacheSystem($api_name, $sql);
			$cache->clear($api_name);
			$cache->setData(json_encode($data));

			$response->withStatus( 200 )->getBody()->write( json_encode( $data ) );

		} else {

			$response->withStatus(400)->getBody()->write(json_encode(array('error' => 'У вас нет прав на изменение данных!')));

		}

		return $response->withHeader('Content-type', 'application/json; charset=UTF-8');
	});
	$this->delete('/{id:[0-9]+}[/]', function (Request $request, Response $response, Array $args) use ($possibleData, $api_name, $connect, $header, $access ) {
		foreach ( $request->getHeaders() as $name => $value ) {
			$name = strtolower(str_replace('HTTP_', '', $name));
			$header[$name] = $value[0];
		}

		$checkAccess = checkAPI($header['x_api_key'], $api_name);
		if (isset($checkAccess['error'])) return $response->withStatus(400)->getBody()->write(json_encode(array('error' => $checkAccess['error'])));

		$access['full'] = $checkAccess['admin'];
		$access['can_delete'] = $checkAccess['delete'];

		if ($access['full'] || $access['can_delete']) {

			$id = $args['id'];
			if (!(int)$id)
				return $response->withStatus(400)->getBody()->write(json_encode(array('error' => 'Требуемая информация отсутствует: ID!')));

			$sql = 'DELETE FROM '. USERPREFIX . "_{$api_name} WHERE id = {$id}";
			$connect->query( $sql );

			$cache = new CacheSystem($api_name, $sql);
			$cache->clear($api_name);

			$response->withStatus( 200 )->getBody()->write( json_encode( array('success' => 'Данные успешно удалены!') ) );

		} else {

			$response->withStatus(400)->getBody()->write(json_encode(array('error' => 'У вас нет прав на удаление данных!')));

		}

		return $response->withHeader('Content-type', 'application/json; charset=UTF-8');
	});

	// Own routing Add
});