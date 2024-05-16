/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

let $addButtonCall = document.getElementById('callAddCollection');

if ($addButtonCall) {
    /* Блок для новой коллекции */
    let $blockCollectionCall = document.getElementById('collection-call');

    if ($blockCollectionCall) {

        $addButtonCall.addEventListener('click', function () {

            let $addButtonCall = this;
            /* получаем прототип коллекции  */
            let newForm = $addButtonCall.dataset.prototype;
            let index = $addButtonCall.dataset.index * 1;

            /* Замена '__name__' в HTML-коде прототипа
            вместо этого будет число, основанное на том, сколько коллекций */
            newForm = newForm.replace(/__call__/g, index);

            /* Вставляем новую коллекцию */
            let div = document.createElement('div');
            div.id = 'item_contacts_region_form_call_' + index;

            // div.classList.add('align-items-center');
            // div.classList.add('gap-3');
            // div.classList.add('item-collection-file');

            div.innerHTML = newForm;
            $blockCollectionCall.append(div);


            /* Добавить контактный номер телефона */
            (div.querySelector('#contacts_region_add_phone'))?.addEventListener('click', addPhone);


            /* Удаляем контактный номер телефона */
            (div.querySelector('.del-item-phone'))?.addEventListener('click', deletePhone);


            (div.querySelector('.del-item-call'))?.addEventListener('click', deleteCall);


            /* Увеличиваем data-index на 1 после вставки новой коллекции */
            $addButtonCall.dataset.index = (index + 1).toString();

            /* Плавная прокрутка к элементу */
            div.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});


            /* Собыьтие на смену обложки */
            let inputElement = div.querySelector('input[type="file"]');

            inputElement.addEventListener('change', function (e) {

                var file = inputElement.files[0];
                var reader = new FileReader();
                let image = div.querySelector('.image-input');

                reader.onloadend = function () {

                    image.style.setProperty("background-image", "url(" + reader.result + ")", "important")
                }

                if (file) {
                    reader.readAsDataURL(file);
                } else {
                    image.style.setProperty("background-image", "url(/img/blank.svg)", "important")

                }
            });

        });
    }
}

/*document.querySelectorAll('.del-item-call').forEach(function (item) {
    item.addEventListener('click', deleteCall);
});*/

/*function deleteCall() {

   if (document.getElementById('collection-call').childElementCount === 1)
   {
       return;
   }

   document.getElementById(this.dataset.delete).remove();
}*/


/** Добавить контактный телефон */

document.querySelectorAll('#contacts_region_add_phone').forEach(function (item) {
    item.addEventListener('click', addPhone);
});

function addPhone() {

    /* Получаем прототип формы */
    let newForm = this.dataset.prototype;
    let index = this.dataset.index * 1;
    let call = this.dataset.call * 1;
    let collection = this.dataset.collection;

    //let id = this.dataset.id;

    // newForm = newForm.replace(/__name__/g, name) /* меняем индекс торгового предложения */

    //newForm = newForm.replace(/__call__/g, call)
    newForm = newForm.replace(/__call_phone__/g, index)

    let div = document.createElement('div');
    div.innerHTML = newForm;
    div.id = 'item_contacts_region_form_calls_phone_' + index;
    div.classList.add('mb-3');

    let $collection = document.getElementById(collection);
    $collection.append(div);

    /* Удаляем контактный номер телефона */
    (div.querySelector('.del-item-phone'))?.addEventListener('click', deletePhone);

    this.dataset.index = (index + 1).toString();

    /** обновляем phone */
    var inputs = document.querySelectorAll('input[type="tel"]');

    Array.prototype.forEach.call(inputs, function(input) {

        if(input.classList.contains('loaded') === false)
        {
            new InputMask(input)
        }
    })
}

document.querySelectorAll('.del-item-phone').forEach(function (item) {
    item.addEventListener('click', deletePhone);
});

function deletePhone() {
    document.getElementById(this.dataset.delete).remove();
}



