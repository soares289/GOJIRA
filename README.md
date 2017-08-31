GOJIRA
======

Framework baseado no padrão MVC para rapido desenvolvimento de sistemas web




TODOS
 - Implementar ROUTES
 - Revisão completa do Loader.js
 - Implemenar criação de tabela automáticamente no log.class.php
 - Tornar conexão com a base de dados opcional
 - Revisar helpers
 - Ver possibilidade de parametos serem posicionais e não nominais
 - Alterar SendMail
      - Verificar se a classe existe
      - De vez de receber parametos opcionais, receber um parameto config
 - Adicionar USES no controller (auto-instanciar model - Pensar se compensa)
 - Adicionar propriedades padrões do controler (Ex: not_auto_render = Não carregar view automaticamente no escopo docontroler e no retorno da action)
 - Documentação
 - Opção de carregar automaticamente data (no model) quando tem chaves estrangeiras (tipo um getAll ou getDeep)
 - Remover libs de terceiro da pasta de lib (Vendor, talvez)
 - Rever todos os componentes de terceiros no framework, se sao realmente necessarios
