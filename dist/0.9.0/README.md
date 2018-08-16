GOJIRA
======

Framework baseado no padrão MVC para rapido desenvolvimento de sistemas web

O principal diferenccial do GOJIRA, é que diferente dos frameworks comuns que criam a base de dados de acordo com os models, ele trabalha no sentido inverso, utilizando uma base de dados já existente e se adaptando a ela. Desenvolvido pensando em um ambiente onde existe um DBA que já modelou a base de dados para o sistema.

- Versão 0.9.0
Muita coisa foi atualizada durante a existencia do GOJIRA. Alguns conceitos alterados, outros aprimorados. Funcionalidades que existiam antes, já não existem hoje e funcionalidades de hoje que não existiam antes.

Como não existia esse controle nas versões anteriores, vou fazer aqui um resumo aqui do que mudou e o status atual dessa versão e oque está previsto para as proximas.


= O status atual do sistema:
=== Engine - Essa classe recebe renderiza os views, na seguinte sequencia: Executa a função no controller, pega o retorno do controller, envia para o view e pega o html do template como retorno e joga para a tela. Se não existir um template para ser renderizado, ele imprime o retorno do controller. Se existir um model para o controller, ele fica instanciado e disponivel durante a execução da procedure chamada do controller.
=== init.php - Esse arquivo inicia todos os objetos padrões utilizados pelo sistema, tais como a conexão, smarty e etc.
=== config.php - Esse arquivo fica no projeto. Ele recebe os parametos da url e chama o init e na sequencia o render. A unica função dele é essa, receber os parametos, localizar o gojira para dar include e passar os parametos recebido para ele. 
=== htaccess - No projeto é bom por um htaccess para facilitar a tradução dos parametos, podendo utilziar url amigaveis, mas não é obrigatorio.
=== components - Isso é uma mão na roda. Eles funcionam bem parecido com um controller. Uma vez instanciado, podem carregar um "model" e utilizar o acesso a base, renderizar views e etc, mas não são acessados por uma url. São uteis para executar funções uteis em vários locais do sistema ou até mesmo serem compartilhados entre diversos projetos.


= Existem uma serie de coisas que vão ser removidas muito em breve do framework.
=== Pasta plugins - Só está lá para manter compatibilidade com versões antigas. A ideia é remover do framework tudo que é especifica para determinados projetos, deixando a cargo do dev que estiver utilizando adicionar os vendors que quiser e não depender do que está dentro do gojira.
=== Pasta LIB - A mesma coisa, é um vendor de terceiro e em breve será removido, hoje só tem nela o nusoap e ele está lá por compatibilidade apenas.
=== Classe de LOG - A ideia era deixar o framework responsável por isso, mas hoje vejo que é má ideia. Isso é muito especifico do cada sistema, não tem como fazer algo generico. A ideia inicial seria logar registros alterados, inclusos ou deletados de todas as tabelas. Pode até vir a ser finalizada no futuro, mas tem que ver se existe a real utilidade disso. Até hoje não existiu a necessidade e então ela não foi finalizada. Pode ser aprimorada para uma classe de debug.
=== Data Dictionary - Será, provavelmente removida. Não existe muita utilidade para ela. Inicialmente ela seria util na utilização do console, mas o proprio console será removido por não ser de fato util.
=== Console - A ideia dele seria criar um componente que criasse os controllers, models e views pela linha de comando, semelhante ao ruby on rails e outros frameworks famosos, mas na verdade, o trampo de criar os controllers e models é tão pequeno que nem vale o trampo de abrir um console só para isso. Talvez no futuro quando o framework tiver mais recursos seja interessante, mas por hora está em standby indeterminado.

= Para versões futuras
=== Um dos objetivos das versões futuras é poder executar o gojira sem necessidade de uma conexão, para poder utilizar seus recursos (que facilitam muito) em projetos que não necessitam de uma conexão constante, isso da mais desempenho. A ideia é ele ficar offline e só conectar mesmo na primeira requisição ao banco de dados. Tem que só dar uma revisada nos models para isso funcionar coretamente.
=== Outro objetivo é deixar o gojira independente de projetos de terceiros, para que ele seja auto-suficiente. Para isso, vamos retirar as pastas de plugins, js, css e etc.
=== A parte de helpers do gojira é bem ruim. Hoje com a implementação dos components, os helpers não são mais tão importantes, porem eventualmente será melhorado, para que, sem muita dor de cabeça, de para criar classes distintas que não funcionem como os controllers e que tenham instancias que podem ser passadas entre os diversos objetos de model, controller e etc.
=== Criar uma documentação, com pelo menos o basico, para que qualquer dev consiga instalar, configurar e utilizar o gojira em seus projetos.
=== Finalizar esses TODOS. Tem muita coisa antiga que talvez nem seja mais utilizado.


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
