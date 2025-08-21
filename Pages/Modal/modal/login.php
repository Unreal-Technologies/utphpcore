<?php
$xhtml = \Utphpcore\Data\Cache::get(\Utphpcore\Core::Xhtml);
$xhtml -> get('body/div@.container', function(\Utphpcore\GUI\NoHtml\Xhtml $container)
{
    $container -> add('div@.col s12/h2') -> text('Login');
    $form = new Utphpcore\GUI\NoHtml\Materialize\Form($container, \Utphpcore\GUI\NoHtml\Materialize\Form\Methods::Post, function(Utphpcore\GUI\NoHtml\Materialize\Form\Options $options)
    {
    });
    $form -> field('username', 'Username', \Utphpcore\GUI\NoHtml\Materialize\Form\InputTypes::Text, '', true, function(Utphpcore\GUI\NoHtml\Materialize\Form\Options $options)
    {
        $options -> size(Utphpcore\GUI\NoHTML\Materialize\Columns::S6);
    });
    $form -> field('password', 'Password', \Utphpcore\GUI\NoHtml\Materialize\Form\InputTypes::Password, '', true, function(Utphpcore\GUI\NoHtml\Materialize\Form\Options $options)
    {
        $options -> size(Utphpcore\GUI\NoHTML\Materialize\Columns::S6);
    });
    $form -> submit('Login');
});