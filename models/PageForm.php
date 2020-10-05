<?php


namespace app\models;


use yii\data\ArrayDataProvider;
use yii\helpers\Html;

class PageForm extends Page
{
    private $_domainFrom;
    private $dbFields = [
        'id',
        'name',
        'date_create',
        'category_id',
        'url_from',
        'user_id'
    ];
    private $calcFields = [
        'domainFrom'
    ];
    private $fields;

    function __construct()
    {
        parent::__construct();
        $this->fields = array_merge($this->dbFields, $this->calcFields);
    }

    public function rules()
    {
        return [
            [['date_create'], 'safe'],
            [['category_id'], 'integer'],
            [['name', 'url_from'], 'string', 'max' => 255],
        ];
    }

    // переопределим  подпись колонки
    public function attributeLabels()
    {
        return ['category_id' => "Category"];
    }


    public function search($params)
    {
        $whereCondition = array();

        foreach ($this->dbFields as $field) {
            if (isset($params['PageForm'][$field]) && ($params['PageForm'][$field] != null)) {
                $_value = Html::encode($params['PageForm'][$field]);
                $whereCondition[$field] = $_value; // поиск пока регистронезависимый
            }
        }

        $query = static::find()
            ->where($whereCondition)
            ->orderBy('date_create DESC');

        $this->scenario = 'filter'; // использование сценариев определяет какие поля выводить в фильтре
        $allData = $query->all();

        // Фильтрация на равенство по расчетным полям
        if ((isset($params['PageForm']['domainFrom'])) && ($params['PageForm']['domainFrom'] != null)) {
            $this->_domainFrom = Html::encode($params['PageForm']['domainFrom']);
            $allData = array_filter($allData, function ($element) {
                return $element->domainFrom == $this->_domainFrom;
            });
        }

        $dataProvider = new ArrayDataProvider([
                'allModels' => $allData,
            ]
        );

        $this->load($params);

        //добавим возможность сортировки
        foreach ($this->fields as $field) {
            $dataProvider->sort->attributes[$field] = [
                'asc' => [$field => SORT_ASC],
                'desc' => [$field => SORT_DESC],
            ];
        }
        if (isset($params['limit']) && ($params['limit'] > 0)) {
            $pageSize = Html::encode($params['limit']);
        } else {
            $pageSize = 25;
        }
        $dataProvider->pagination = ['pageSize' => $pageSize];
        return $dataProvider;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['filter'] = $this->fields;
        return $scenarios;
    }
}