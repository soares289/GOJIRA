GOJIRA
======

Framework baseado no padrão MVC para rapido desenvolvimento de sistemas web




TODOS
 - Ver possibilidade de parametos serem posicionais e não nominais
 - Alterar SendMail
      - Verificar se a classe existe
      - De vez de receber parametos opcionais, receber um parameto config
 - Alterar a forma como o app_configs.ini funciona
      - Ele tem que ser feito de uma forma que em qualquer ambiente funcione, independente da estrutura de pasta anterior a raiz do app.
      - Se possivel, o mesmo com a url
 - Remover o ENGINE.PHP do APP, deixar interno no framework
 - Implementar ROUTES
 - Adicionar USES no controller (auto-instanciar model - Pensar se compensa)
 - Adicionar propriedades padrões do controler (Ex: not_auto_render = Não carregar view automaticamente no escopo docontroler e no retorno da action)
 - Compatibilizar com PHP 7.0
 - Documentação
 - Opção de carregar automaticamente data (no model) quando tem chaves estrangeiras (tipo um getAll ou getDeep)
 - Remover libs de terceiro da pasta de lib (Vendor, talvez)
 - Rever todos os componentes de terceiros no framework, se sao realmente necessarios
 - Quando integrar o ENGINE.php no system, desconectar automaticamente a base de dados, já que não está mais no destruct do objeto de conexão
