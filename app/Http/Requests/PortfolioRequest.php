<?php

namespace Corp\Http\Requests;

use Corp\Http\Requests\Request;

class PortfolioRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::user()->canDo('ADD_PORTFOLIOS');
    }

    public function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        //Валидировать данные поля alias, если они были введены юзером, так же и с пустотой:
        $validator->sometimes('alias', 'unique:portfolios|max:255', function ($input){

            //Если в передаваемом текущем маршруте есть параметр portfolios, значит пользователь ввел alias:
            if($this->route()->hasParameter('portfolios')){
                //Сохраняем в переменную модель текущей записи Portfolio, получаемого из текущего маршрута:
                $model = $this->route()->parameters('portfolios');

                //далее вернем результат сравнения алиаса записи и введенной пользователем, если пользователь и не пустоту ввел:
                return ($model->alias !== $input->alias) && !empty($input->alias);
            }

            return !empty($input->alias);
        });


        return $validator;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'text' => 'required',
            'filter_alias' => 'required|string'
        ];
    }
}
