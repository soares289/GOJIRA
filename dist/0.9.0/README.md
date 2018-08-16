GOJIRA
======

Framework baseado no padrão MVC para rapido desenvolvimento de sistemas web

O principal diferenccial do GOJIRA, é que diferente dos frameworks comuns que criam a base de dados de acordo com os models, ele trabalha no sentido inverso, utilizando uma base de dados já existente e se adaptando a ela. Desenvolvido pensando em um ambiente onde existe um DBA que já modelou a base de dados para o sistema.



TODOS
 - Implementar ROUTES
 - Aprimorar controlle de erros
      - Adicionar uso de set_error_handler para todos os controllers/models instanciados
      - Adicionar mais tratamento de exception
      - Adicionar opção de logar erros no arquivo
 - Revisão completa do Loader.js
 - Implemenar criação de tabela automáticamente no log.class.php
 - Tornar conexão com a base de dados opcional
 - Revisar helpers
 - Ver possibilidade de parametos serem posicionais e não nominais
 - Alterar SendMail
      - Verificar se a classe existe
      - De vez de receber parametos opcionais, receber um parameto config
 - Adicionar USES (ex: use modelX, use modelY) no controller (auto-instanciar model - Pensar se compensa)
 - Adicionar propriedades padrões do controler (Ex: not_auto_render = Não carregar view automaticamente no escopo docontroler e no retorno da action)
 - Documentação
 - Opção de carregar automaticamente data (no model) quando tem chaves estrangeiras (tipo um getAll ou getDeep)
 - Remover libs de terceiro da pasta de lib (Vendor, talvez)
 - Rever todos os componentes de terceiros no framework, se sao realmente necessarios
