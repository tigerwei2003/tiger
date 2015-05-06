<?php if (!defined('THINK_PATH')) exit();
 return array (
  'parent' => 
  array (
    1 => 
    array (
      'id' => '1',
      'url' => '#',
      'name' => '',
      'title' => '资源管理',
      'status' => '1',
      'remark' => '后台资源管理',
      'sort' => '1',
      'pid' => '0',
      'level' => '1',
    ),
    6 => 
    array (
      'id' => '6',
      'url' => '#',
      'name' => '',
      'title' => '充值卡管理',
      'status' => '1',
      'remark' => '充值卡顶级菜单',
      'sort' => '1',
      'pid' => '0',
      'level' => '1',
    ),
    15 => 
    array (
      'id' => '15',
      'url' => '#',
      'name' => '',
      'title' => '系统管理',
      'status' => '1',
      'remark' => '系统管理',
      'sort' => '11',
      'pid' => '0',
      'level' => '1',
    ),
    19 => 
    array (
      'id' => '19',
      'url' => '#',
      'name' => '',
      'title' => '数据统计',
      'status' => '1',
      'remark' => '数据统计相关',
      'sort' => '21',
      'pid' => '0',
      'level' => '1',
    ),
    20 => 
    array (
      'id' => '20',
      'url' => '#',
      'name' => '',
      'title' => '擂台管理',
      'status' => '1',
      'remark' => '擂台相关管理',
      'sort' => '31',
      'pid' => '0',
      'level' => '1',
    ),
    21 => 
    array (
      'id' => '21',
      'url' => '#',
      'name' => '',
      'title' => '管理工具',
      'status' => '1',
      'remark' => '管理工具',
      'sort' => '41',
      'pid' => '0',
      'level' => '1',
    ),
    43 => 
    array (
      'id' => '43',
      'url' => '#',
      'name' => '',
      'title' => '日志管理',
      'status' => '1',
      'remark' => '',
      'sort' => '51',
      'pid' => '0',
      'level' => '1',
    ),
  ),
  'func' => 
  array (
    2 => 
    array (
      0 => 
      array (
        'id' => '5',
        'url' => 'Node/add',
        'name' => 'add',
        'title' => '添加节点',
        'status' => '1',
        'remark' => '添加节点功能',
        'sort' => '1',
        'pid' => '2',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '54',
        'url' => 'Node/modify',
        'name' => 'modify',
        'title' => '编辑节点',
        'status' => '1',
        'remark' => '编辑节点信息',
        'sort' => '11',
        'pid' => '2',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '55',
        'url' => 'Node/delete',
        'name' => 'delete',
        'title' => '删除节点',
        'status' => '1',
        'remark' => '删除节点(谨慎操作)',
        'sort' => '21',
        'pid' => '2',
        'level' => '3',
      ),
    ),
    3 => 
    array (
      0 => 
      array (
        'id' => '56',
        'url' => 'Role/add',
        'name' => 'add',
        'title' => '创建角色',
        'status' => '1',
        'remark' => '创建角色',
        'sort' => '1',
        'pid' => '3',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '57',
        'url' => 'Role/modify',
        'name' => 'modify',
        'title' => '编辑角色',
        'status' => '1',
        'remark' => '编辑角色信息',
        'sort' => '11',
        'pid' => '3',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '58',
        'url' => 'Role/priv',
        'name' => 'priv',
        'title' => '角色授权',
        'status' => '1',
        'remark' => '角色授权',
        'sort' => '21',
        'pid' => '3',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '59',
        'url' => 'Role/delete',
        'name' => 'delete',
        'title' => '删除角色',
        'status' => '1',
        'remark' => '删除角色信息(谨慎操作)',
        'sort' => '31',
        'pid' => '3',
        'level' => '3',
      ),
    ),
    4 => 
    array (
      0 => 
      array (
        'id' => '60',
        'url' => 'Admin/add',
        'name' => 'add',
        'title' => '添加管理员',
        'status' => '1',
        'remark' => '添加管理员',
        'sort' => '1',
        'pid' => '4',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '61',
        'url' => 'Admin/edit',
        'name' => 'edit',
        'title' => '编辑用户信息',
        'status' => '1',
        'remark' => '编辑用户信息',
        'sort' => '2',
        'pid' => '4',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '62',
        'url' => 'Admin/password',
        'name' => 'password',
        'title' => '修改用户密码',
        'status' => '1',
        'remark' => '修改用户密码',
        'sort' => '3',
        'pid' => '4',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '63',
        'url' => 'Admin/delete',
        'name' => 'delete',
        'title' => '删除用户',
        'status' => '1',
        'remark' => '删除用户信息',
        'sort' => '4',
        'pid' => '4',
        'level' => '3',
      ),
    ),
    11 => 
    array (
      0 => 
      array (
        'id' => '64',
        'url' => 'Dealer/add',
        'name' => 'add',
        'title' => '添加渠道',
        'status' => '1',
        'remark' => '添加渠道',
        'sort' => '1',
        'pid' => '11',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '65',
        'url' => 'Dealer/edit',
        'name' => 'edit',
        'title' => '修改渠道信息',
        'status' => '1',
        'remark' => '修改渠道信息',
        'sort' => '2',
        'pid' => '11',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '66',
        'url' => 'Dealer/delete',
        'name' => 'delete',
        'title' => '删除渠道',
        'status' => '1',
        'remark' => '删除渠道信息',
        'sort' => '3',
        'pid' => '11',
        'level' => '3',
      ),
    ),
    9 => 
    array (
      0 => 
      array (
        'id' => '75',
        'url' => 'Export/soldcard',
        'name' => 'soldcard',
        'title' => '导出',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '9',
        'level' => '3',
      ),
    ),
    10 => 
    array (
      0 => 
      array (
        'id' => '76',
        'url' => 'Rechargecard/index',
        'name' => 'index',
        'title' => '已售充值卡',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '10',
        'level' => '3',
      ),
    ),
    16 => 
    array (
      0 => 
      array (
        'id' => '77',
        'url' => 'Export/account',
        'name' => 'account',
        'title' => '导出',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '16',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '78',
        'url' => 'Account/edit',
        'name' => 'edit',
        'title' => '编辑账户',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '16',
        'level' => '3',
      ),
    ),
    17 => 
    array (
      0 => 
      array (
        'id' => '79',
        'url' => 'Game/add',
        'name' => 'add',
        'title' => '添加游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '17',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '80',
        'url' => 'Delete/games',
        'name' => 'games',
        'title' => '删除游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '17',
        'level' => '3',
      ),
    ),
    18 => 
    array (
      0 => 
      array (
        'id' => '81',
        'url' => 'Game/gamecategory_category_edit',
        'name' => 'gamecategory_category_edit',
        'title' => '增加游戏类别',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '18',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '82',
        'url' => 'Delete/gamecategory',
        'name' => 'gamecategory',
        'title' => '删除游戏类别',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '18',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '83',
        'url' => 'Game/gamecategory_game_add',
        'name' => 'gamecategory_game_add',
        'title' => '增加游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '18',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '84',
        'url' => 'Delete/gamecategory_game',
        'name' => 'gamecategory_game',
        'title' => '删除类别中游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '4',
        'pid' => '18',
        'level' => '3',
      ),
    ),
    35 => 
    array (
      0 => 
      array (
        'id' => '85',
        'url' => 'Game/gamepack_edit',
        'name' => 'gamepack_edit',
        'title' => '增加游戏包',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '35',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '86',
        'url' => 'Delete/gamepack',
        'name' => 'gamepack',
        'title' => '删除游戏包',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '35',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '87',
        'url' => 'Game/gamepack_game_add',
        'name' => 'gamepack_game_add',
        'title' => '游戏包添加游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '35',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '88',
        'url' => 'Delete/gamepack_game',
        'name' => 'gamepack_game',
        'title' => '删除游戏包游戏',
        'status' => '1',
        'remark' => '',
        'sort' => '4',
        'pid' => '35',
        'level' => '3',
      ),
    ),
    36 => 
    array (
      0 => 
      array (
        'id' => '89',
        'url' => 'Game/recommends_edit',
        'name' => 'recommends_edit',
        'title' => '编辑推荐',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '36',
        'level' => '3',
      ),
    ),
    37 => 
    array (
      0 => 
      array (
        'id' => '90',
        'url' => 'Delete/chargepoint',
        'name' => 'chargepoint',
        'title' => '删除计费点',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '37',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '91',
        'url' => 'Game/chargepoint_edit',
        'name' => 'chargepoint_edit',
        'title' => '增加计费点',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '37',
        'level' => '3',
      ),
    ),
    38 => 
    array (
      0 => 
      array (
        'id' => '92',
        'url' => 'Game/clientver_edit',
        'name' => 'clientver_edit',
        'title' => '编辑客户端信息',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '38',
        'level' => '3',
      ),
    ),
    39 => 
    array (
      0 => 
      array (
        'id' => '93',
        'url' => 'Game/server_edit',
        'name' => 'server_edit',
        'title' => '增加服务器信息',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '39',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '94',
        'url' => 'Delete/server',
        'name' => 'server',
        'title' => '删除服务器信息',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '39',
        'level' => '3',
      ),
    ),
    40 => 
    array (
      0 => 
      array (
        'id' => '95',
        'url' => 'Game/area_edit',
        'name' => 'area_edit',
        'title' => '编辑区域',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '40',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '96',
        'url' => 'Delete/area',
        'name' => 'area',
        'title' => '删除区域',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '40',
        'level' => '3',
      ),
    ),
    41 => 
    array (
      0 => 
      array (
        'id' => '97',
        'url' => 'Export/hardware_ex',
        'name' => 'hardware_ex',
        'title' => '导出硬件信息',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '41',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '98',
        'url' => 'Game/hardware_edit',
        'name' => 'hardware_edit',
        'title' => '编辑硬件信息',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '41',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '99',
        'url' => 'Delete/hardware',
        'name' => 'hardware',
        'title' => '删除硬件信息',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '41',
        'level' => '3',
      ),
    ),
    68 => 
    array (
      0 => 
      array (
        'id' => '100',
        'url' => 'Export/code',
        'name' => 'code',
        'title' => '导出',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '68',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '101',
        'url' => 'Code/rechargecard_edit',
        'name' => 'rechargecard_edit',
        'title' => '编辑',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '68',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '102',
        'url' => 'Delete/rechargecard',
        'name' => 'rechargecard',
        'title' => '删除充值卡',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '68',
        'level' => '3',
      ),
    ),
    69 => 
    array (
      0 => 
      array (
        'id' => '103',
        'url' => 'Export/exchange',
        'name' => 'exchange',
        'title' => '导出',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '69',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '104',
        'url' => 'Code/exchange_edit',
        'name' => 'exchange_edit',
        'title' => '生成特殊卡',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '69',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '105',
        'url' => 'Code/exchange_type',
        'name' => 'exchange_type',
        'title' => '特殊卡类别',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '69',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '106',
        'url' => 'Delete/exchange',
        'name' => 'exchange',
        'title' => '删除',
        'status' => '1',
        'remark' => '',
        'sort' => '4',
        'pid' => '69',
        'level' => '3',
      ),
      4 => 
      array (
        'id' => '115',
        'url' => 'Code/exchange_type_edit',
        'name' => 'exchange_type_edit',
        'title' => '特殊卡类型编辑',
        'status' => '1',
        'remark' => '',
        'sort' => '5',
        'pid' => '69',
        'level' => '3',
      ),
      5 => 
      array (
        'id' => '116',
        'url' => 'Code/show_record',
        'name' => 'show_record',
        'title' => '特殊卡使用记录',
        'status' => '1',
        'remark' => '',
        'sort' => '6',
        'pid' => '69',
        'level' => '3',
      ),
      6 => 
      array (
        'id' => '117',
        'url' => 'Code/exchange_type_mark',
        'name' => 'exchange_type_mark',
        'title' => '特殊卡类别标 识',
        'status' => '1',
        'remark' => '',
        'sort' => '7',
        'pid' => '69',
        'level' => '3',
      ),
    ),
    22 => 
    array (
      0 => 
      array (
        'id' => '107',
        'url' => 'Arena/arena_edit',
        'name' => 'arena_edit',
        'title' => '编辑',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '22',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '108',
        'url' => 'Arena/arena_rank',
        'name' => 'arena_rank',
        'title' => '擂主列表',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '22',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '109',
        'url' => 'Arena/arena_queue',
        'name' => 'arena_queue',
        'title' => '排队列表',
        'status' => '1',
        'remark' => '',
        'sort' => '3',
        'pid' => '22',
        'level' => '3',
      ),
      3 => 
      array (
        'id' => '110',
        'url' => 'Arena/arena_watcher',
        'name' => 'arena_watcher',
        'title' => '擂台实时数据',
        'status' => '1',
        'remark' => '',
        'sort' => '4',
        'pid' => '22',
        'level' => '3',
      ),
    ),
    23 => 
    array (
      0 => 
      array (
        'id' => '111',
        'url' => 'Arena/arena_support',
        'name' => 'arena_support',
        'title' => '支持列表',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '23',
        'level' => '3',
      ),
    ),
    24 => 
    array (
      0 => 
      array (
        'id' => '112',
        'url' => 'Arena/arena_game_edit',
        'name' => 'arena_game_edit',
        'title' => '编辑',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '24',
        'level' => '3',
      ),
    ),
    45 => 
    array (
      0 => 
      array (
        'id' => '113',
        'url' => 'Tools/check_actid_exist',
        'name' => 'check_actid_exist',
        'title' => '查看账号是否存在',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '45',
        'level' => '3',
      ),
      1 => 
      array (
        'id' => '114',
        'url' => 'Tools/game_list',
        'name' => 'game_list',
        'title' => '获取游戏列表',
        'status' => '1',
        'remark' => '',
        'sort' => '2',
        'pid' => '45',
        'level' => '3',
      ),
      2 => 
      array (
        'id' => '123',
        'url' => 'Tools/upload_gamesave_do',
        'name' => 'upload_gamesave_do',
        'title' => '上传用户存档',
        'status' => '1',
        'remark' => '上传存档具体操作方法',
        'sort' => '3',
        'pid' => '45',
        'level' => '3',
      ),
    ),
    119 => 
    array (
      0 => 
      array (
        'id' => '120',
        'url' => 'Arena/push_comments',
        'name' => '发布吐槽',
        'title' => '',
        'status' => '1',
        'remark' => '',
        'sort' => '1',
        'pid' => '119',
        'level' => '3',
      ),
    ),
  ),
  'child' => 
  array (
    6 => 
    array (
      0 => 
      array (
        'id' => '9',
        'url' => 'Rechargecard/index',
        'name' => 'index',
        'title' => '已售充值卡',
        'status' => '1',
        'remark' => '已经销售的充值卡信息查询',
        'sort' => '1',
        'pid' => '6',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '10',
        'url' => 'Rechargecard/activation',
        'name' => 'activation',
        'title' => '激活充值卡',
        'status' => '1',
        'remark' => '后台激活充值卡',
        'sort' => '11',
        'pid' => '6',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '67',
        'url' => 'Rechargecard/countchart',
        'name' => 'countchart',
        'title' => '售卡汇总',
        'status' => '1',
        'remark' => '售卡汇总',
        'sort' => '21',
        'pid' => '6',
        'level' => '2',
      ),
    ),
    15 => 
    array (
      0 => 
      array (
        'id' => '16',
        'url' => 'Account/index',
        'name' => 'index',
        'title' => '账号管理',
        'status' => '1',
        'remark' => '注册账号管理',
        'sort' => '1',
        'pid' => '15',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '17',
        'url' => 'Game/index',
        'name' => 'index',
        'title' => '游戏管理',
        'status' => '1',
        'remark' => '游戏信息管理',
        'sort' => '11',
        'pid' => '15',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '18',
        'url' => 'Game/gamecategory',
        'name' => 'gamecategory',
        'title' => '游戏类别管理',
        'status' => '1',
        'remark' => '游戏类别信息管理',
        'sort' => '21',
        'pid' => '15',
        'level' => '2',
      ),
      3 => 
      array (
        'id' => '35',
        'url' => 'Game/gamepack',
        'name' => 'gamepack',
        'title' => '游戏包管理',
        'status' => '1',
        'remark' => '游戏包管理',
        'sort' => '31',
        'pid' => '15',
        'level' => '2',
      ),
      4 => 
      array (
        'id' => '36',
        'url' => 'Game/recommends',
        'name' => 'recommends',
        'title' => '推荐管理',
        'status' => '1',
        'remark' => '推荐游戏管理',
        'sort' => '41',
        'pid' => '15',
        'level' => '2',
      ),
      5 => 
      array (
        'id' => '37',
        'url' => 'Game/chargepoint',
        'name' => 'chargepoint',
        'title' => '计费点管理',
        'status' => '1',
        'remark' => '计费点管理',
        'sort' => '51',
        'pid' => '15',
        'level' => '2',
      ),
      6 => 
      array (
        'id' => '38',
        'url' => 'Game/clientver',
        'name' => 'clientver',
        'title' => '客户端管理',
        'status' => '1',
        'remark' => '客户端版本管理',
        'sort' => '61',
        'pid' => '15',
        'level' => '2',
      ),
      7 => 
      array (
        'id' => '39',
        'url' => 'Game/server',
        'name' => 'server',
        'title' => '服务器管理',
        'status' => '1',
        'remark' => '服务器管理',
        'sort' => '71',
        'pid' => '15',
        'level' => '2',
      ),
      8 => 
      array (
        'id' => '40',
        'url' => 'Game/area',
        'name' => 'area',
        'title' => '区域管理',
        'status' => '1',
        'remark' => '区域管理',
        'sort' => '81',
        'pid' => '15',
        'level' => '2',
      ),
      9 => 
      array (
        'id' => '41',
        'url' => 'Game/hardware',
        'name' => 'hardware',
        'title' => '硬件信息管理',
        'status' => '1',
        'remark' => '硬件信息管理',
        'sort' => '91',
        'pid' => '15',
        'level' => '2',
      ),
      10 => 
      array (
        'id' => '42',
        'url' => 'Game/gamesave',
        'name' => 'gamesave',
        'title' => '存档管理',
        'status' => '1',
        'remark' => '存档管理',
        'sort' => '101',
        'pid' => '15',
        'level' => '2',
      ),
      11 => 
      array (
        'id' => '68',
        'url' => 'Code/rechargecard',
        'name' => 'rechargecard',
        'title' => '充值卡管理',
        'status' => '1',
        'remark' => '',
        'sort' => '111',
        'pid' => '15',
        'level' => '2',
      ),
      12 => 
      array (
        'id' => '69',
        'url' => 'Code/exchange',
        'name' => 'exchange',
        'title' => '特殊卡管理',
        'status' => '1',
        'remark' => '',
        'sort' => '121',
        'pid' => '15',
        'level' => '2',
      ),
      13 => 
      array (
        'id' => '124',
        'url' => 'Checkcard/Index',
        'name' => 'Index',
        'title' => '多选卡管理',
        'status' => '1',
        'remark' => '一卡多选',
        'sort' => '122',
        'pid' => '15',
        'level' => '2',
      ),
      14 => 
      array (
        'id' => '121',
        'url' => 'System/pid_logo',
        'name' => '渠道LOGO管理',
        'title' => '渠道LOGO管理',
        'status' => '1',
        'remark' => '',
        'sort' => '131',
        'pid' => '15',
        'level' => '2',
      ),
    ),
    20 => 
    array (
      0 => 
      array (
        'id' => '22',
        'url' => 'Arena/index',
        'name' => 'Arena',
        'title' => '擂台管理',
        'status' => '1',
        'remark' => '擂台相关管理',
        'sort' => '1',
        'pid' => '20',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '23',
        'url' => 'Arena/arena_battle',
        'name' => 'arena_battle',
        'title' => '擂台战斗管理',
        'status' => '1',
        'remark' => '擂台战斗管理',
        'sort' => '11',
        'pid' => '20',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '24',
        'url' => 'Arena/arena_game',
        'name' => 'arena_game',
        'title' => '擂台游戏管理',
        'status' => '1',
        'remark' => '擂台游戏管理',
        'sort' => '21',
        'pid' => '20',
        'level' => '2',
      ),
      3 => 
      array (
        'id' => '25',
        'url' => 'Arena/chart_arena',
        'name' => 'chart_arena',
        'title' => '擂台每日统计',
        'status' => '1',
        'remark' => '擂台每日统计',
        'sort' => '31',
        'pid' => '20',
        'level' => '2',
      ),
      4 => 
      array (
        'id' => '70',
        'url' => 'Arena/arena_account',
        'name' => 'arena_account',
        'title' => '擂台用户管理',
        'status' => '1',
        'remark' => '',
        'sort' => '41',
        'pid' => '20',
        'level' => '2',
      ),
      5 => 
      array (
        'id' => '119',
        'url' => 'Arena/get_recent_comments',
        'name' => '擂台吐槽管理',
        'title' => '擂台吐槽管理',
        'status' => '1',
        'remark' => '',
        'sort' => '51',
        'pid' => '20',
        'level' => '2',
      ),
    ),
    21 => 
    array (
      0 => 
      array (
        'id' => '26',
        'url' => 'Tools/upload_avatar',
        'name' => 'upload_avatar',
        'title' => '头像上传',
        'status' => '1',
        'remark' => '用户头像上传',
        'sort' => '1',
        'pid' => '21',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '45',
        'url' => 'Tools/upload_show',
        'name' => 'upload_show',
        'title' => '存档上传',
        'status' => '1',
        'remark' => '存档上传',
        'sort' => '11',
        'pid' => '21',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '46',
        'url' => 'Tools/recharge',
        'name' => 'recharge',
        'title' => '用户充值',
        'status' => '1',
        'remark' => '用户充值',
        'sort' => '21',
        'pid' => '21',
        'level' => '2',
      ),
      3 => 
      array (
        'id' => '47',
        'url' => 'Tools/copy_gamesave_show',
        'name' => 'copy_gamesave_show',
        'title' => '存档复制',
        'status' => '1',
        'remark' => '存档复制',
        'sort' => '31',
        'pid' => '21',
        'level' => '2',
      ),
    ),
    43 => 
    array (
      0 => 
      array (
        'id' => '44',
        'url' => 'Log/index',
        'name' => 'index',
        'title' => '系统日志',
        'status' => '1',
        'remark' => '系统日志',
        'sort' => '1',
        'pid' => '43',
        'level' => '2',
      ),
    ),
    1 => 
    array (
      0 => 
      array (
        'id' => '2',
        'url' => 'Node/index',
        'name' => 'Node',
        'title' => '节点管理',
        'status' => '1',
        'remark' => '节点菜单管理',
        'sort' => '11',
        'pid' => '1',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '3',
        'url' => 'Role/index',
        'name' => 'Role',
        'title' => '角色管理',
        'status' => '1',
        'remark' => '系统角色管理',
        'sort' => '11',
        'pid' => '1',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '4',
        'url' => 'Admin/index',
        'name' => 'Admin',
        'title' => '用户管理',
        'status' => '1',
        'remark' => '后台用户管理',
        'sort' => '11',
        'pid' => '1',
        'level' => '2',
      ),
      3 => 
      array (
        'id' => '11',
        'url' => 'Dealer/index',
        'name' => 'index',
        'title' => '渠道管理',
        'status' => '1',
        'remark' => '渠道列表',
        'sort' => '21',
        'pid' => '1',
        'level' => '2',
      ),
    ),
    19 => 
    array (
      0 => 
      array (
        'id' => '28',
        'url' => 'Record/gametimes',
        'name' => 'gametimes',
        'title' => '游戏时间记录',
        'status' => '1',
        'remark' => '游戏时间记录',
        'sort' => '11',
        'pid' => '19',
        'level' => '2',
      ),
      1 => 
      array (
        'id' => '29',
        'url' => 'Record/gamepack',
        'name' => 'gamepack',
        'title' => '游戏包购买记录',
        'status' => '1',
        'remark' => '游戏包购买记录',
        'sort' => '21',
        'pid' => '19',
        'level' => '2',
      ),
      2 => 
      array (
        'id' => '30',
        'url' => 'Record/payment',
        'name' => 'payment',
        'title' => '虚拟币消费记录',
        'status' => '1',
        'remark' => '虚拟币消费记录',
        'sort' => '31',
        'pid' => '19',
        'level' => '2',
      ),
      3 => 
      array (
        'id' => '31',
        'url' => 'Record/income',
        'name' => 'income',
        'title' => '虚拟币收入记录',
        'status' => '1',
        'remark' => '虚拟币收入记录',
        'sort' => '41',
        'pid' => '19',
        'level' => '2',
      ),
      4 => 
      array (
        'id' => '32',
        'url' => 'Record/order',
        'name' => 'order',
        'title' => '订单记录',
        'status' => '1',
        'remark' => '订单记录',
        'sort' => '51',
        'pid' => '19',
        'level' => '2',
      ),
      5 => 
      array (
        'id' => '33',
        'url' => 'Record/sign_in',
        'name' => 'sign_in',
        'title' => '签到记录',
        'status' => '1',
        'remark' => '签到记录',
        'sort' => '61',
        'pid' => '19',
        'level' => '2',
      ),
      6 => 
      array (
        'id' => '34',
        'url' => 'Record/device',
        'name' => 'device',
        'title' => '设备列表',
        'status' => '1',
        'remark' => '设备记录',
        'sort' => '71',
        'pid' => '19',
        'level' => '2',
      ),
      7 => 
      array (
        'id' => '48',
        'url' => 'Chart/game',
        'name' => 'game',
        'title' => '每日游戏统计',
        'status' => '1',
        'remark' => '每日游戏统计',
        'sort' => '81',
        'pid' => '19',
        'level' => '2',
      ),
      8 => 
      array (
        'id' => '49',
        'url' => 'Chart/region',
        'name' => 'region',
        'title' => '每日区域统计',
        'status' => '1',
        'remark' => '',
        'sort' => '91',
        'pid' => '19',
        'level' => '2',
      ),
      9 => 
      array (
        'id' => '50',
        'url' => 'Chart/pid',
        'name' => 'pid',
        'title' => '每日渠道统计',
        'status' => '1',
        'remark' => '每日渠道统计',
        'sort' => '101',
        'pid' => '19',
        'level' => '2',
      ),
      10 => 
      array (
        'id' => '51',
        'url' => 'Chart/nettest',
        'name' => 'nettest',
        'title' => '测速统计',
        'status' => '1',
        'remark' => '测速统计',
        'sort' => '111',
        'pid' => '19',
        'level' => '2',
      ),
      11 => 
      array (
        'id' => '52',
        'url' => 'Chart/statistics',
        'name' => 'statistics',
        'title' => '数据汇总',
        'status' => '1',
        'remark' => '数据汇总',
        'sort' => '121',
        'pid' => '19',
        'level' => '2',
      ),
      12 => 
      array (
        'id' => '53',
        'url' => 'Chart/index',
        'name' => 'index',
        'title' => '每日统计',
        'status' => '1',
        'remark' => '每日统计',
        'sort' => '131',
        'pid' => '19',
        'level' => '2',
      ),
    ),
  ),
) ?>