# Requirements Document

## Introduction

Este documento define os requisitos para implementar um sistema completo de protocolos e gestão de usuários para uma organização com estrutura hierárquica de assembleias e jurisdições. O sistema deve gerenciar diferentes tipos de usuários (Menina Ativa, Maioridade, Tio Maçom, Tia Estrela do Oriente, Tio/Tia), níveis de acesso (Admin Assembleia, Membro Jurisdição, Membro), cargos organizacionais, e diversos tipos de protocolos para cerimônias, honrarias e gestão administrativa.

## Requirements

### Requirement 1 - Sistema de Tipos de Usuário

**User Story:** Como administrador do sistema, eu quero gerenciar diferentes tipos de usuários com suas características específicas, para que cada membro seja cadastrado com as informações apropriadas ao seu perfil.

#### Acceptance Criteria

1. WHEN um usuário do tipo "Menina Ativa" é cadastrado THEN o sistema SHALL exigir assembleia, data de iniciação e informações pessoais completas
2. WHEN um usuário do tipo "Maioridade" é cadastrado THEN o sistema SHALL exigir assembleia, data de iniciação e informações pessoais completas
3. WHEN um usuário do tipo "Tio Maçom" é cadastrado THEN o sistema SHALL exigir nome da Loja Maçônica, data de iniciação, graus maçônicos e não exigir informações de pais/responsáveis
4. WHEN um usuário do tipo "Tia Estrela do Oriente" é cadastrado THEN o sistema SHALL exigir nome do Capítulo, data de iniciação, opcionalmente data de iniciação no arco-íris, e não exigir informações de pais/responsáveis
5. WHEN um usuário do tipo "Tio/Tia" é cadastrado THEN o sistema SHALL exigir apenas informações básicas sem pais/responsáveis
6. WHEN um Tio Maçom seleciona grau "Mestre" THEN o sistema SHALL exigir data que virou mestre e data que virou companheiro
7. WHEN um Tio Maçom seleciona grau "Companheiro" THEN o sistema SHALL exigir data que virou companheiro
8. WHEN um Tio Maçom é iniciado THEN o sistema SHALL automaticamente registrar como "Aprendiz" com a data de iniciação

### Requirement 2 - Sistema de Níveis de Acesso

**User Story:** Como administrador do sistema, eu quero controlar os níveis de acesso dos usuários, para que cada pessoa tenha permissões apropriadas às suas responsabilidades.

#### Acceptance Criteria

1. WHEN um usuário é definido como "Admin Assembleia" THEN o sistema SHALL permitir acesso completo aos protocolos da sua assembleia
2. WHEN um usuário é definido como "Membro Jurisdição" THEN o sistema SHALL permitir aprovar protocolos de qualquer assembleia e selecionar assembleia ao criar protocolos
3. WHEN um usuário é definido como "Membro" THEN o sistema SHALL permitir apenas visualização de informações da sua assembleia
4. WHEN um cargo do conselho consultivo é "Presidente do Conselho Consultivo" THEN o sistema SHALL automaticamente conceder acesso de Admin Assembleia
5. WHEN um cargo do conselho consultivo é "Preceptora Mãe" ou "Preceptora Mãe Adjunta" THEN o sistema SHALL automaticamente conceder acesso de Admin Assembleia

### Requirement 3 - Sistema de Cargos

**User Story:** Como membro da jurisdição, eu quero gerenciar cargos organizacionais através de protocolos, para que as responsabilidades sejam distribuídas formalmente.

#### Acceptance Criteria

1. WHEN cargos de assembleia são definidos THEN o sistema SHALL permitir apenas meninas ativas nos cargos específicos da assembleia
2. WHEN cargos do conselho consultivo são definidos THEN o sistema SHALL permitir apenas tipos de usuário elegíveis (Tios Maçons, Tias Estrela do Oriente, Maioridades, Tias, Tios)
3. WHEN o cargo "Presidente do Conselho Consultivo" é atribuído THEN o sistema SHALL verificar que o usuário é Tio Maçom com grau Mestre
4. WHEN um protocolo de novos cargos é aprovado THEN o sistema SHALL atualizar automaticamente os cargos e garantir unicidade (apenas 1 pessoa por cargo)
5. WHEN um usuário recebe cargo de jurisdição THEN o sistema SHALL atualizar suas permissões automaticamente

### Requirement 4 - Protocolo de Cerimônia de Maioridade

**User Story:** Como admin da assembleia, eu quero criar protocolos de cerimônia de maioridade, para que meninas ativas possam ser promovidas formalmente.

#### Acceptance Criteria

1. WHEN um protocolo de maioridade é criado THEN o sistema SHALL exibir apenas meninas ativas da assembleia
2. WHEN meninas são selecionadas para maioridade THEN o sistema SHALL permitir edição da lista antes do envio
3. WHEN o protocolo é enviado para aprovação THEN o sistema SHALL encaminhar para membro da jurisdição
4. WHEN o protocolo é aprovado THEN o sistema SHALL exigir data da cerimônia
5. WHEN o protocolo é rejeitado THEN o sistema SHALL exigir feedback e permitir correção pelo admin
6. WHEN o protocolo é concluído THEN o sistema SHALL salvar a data da cerimônia no banco de dados

### Requirement 5 - Protocolo de Iniciação

**User Story:** Como admin da assembleia, eu quero registrar novas iniciações, para que novos membros sejam cadastrados no sistema.

#### Acceptance Criteria

1. WHEN um protocolo de iniciação é criado THEN o sistema SHALL exigir todos os dados pessoais obrigatórios
2. WHEN uma madrinha é selecionada THEN o sistema SHALL validar que é membro ativo da assembleia
3. WHEN o protocolo é aprovado THEN o sistema SHALL criar automaticamente os perfis das novas meninas
4. WHEN os perfis são criados THEN o sistema SHALL enviar e-mail de primeiro acesso para todas as meninas
5. WHEN dados são inseridos THEN o sistema SHALL permitir adicionar múltiplas meninas sem limite máximo
6. WHEN o formulário é preenchido THEN o sistema SHALL limpar campos após adicionar cada menina

### Requirement 6 - Protocolos de Honrarias (Homenageados do Ano)

**User Story:** Como admin da assembleia, eu quero processar honrarias anuais, para que membros sejam reconhecidos formalmente.

#### Acceptance Criteria

1. WHEN um protocolo de homenageados é criado THEN o sistema SHALL permitir seleção de membros da assembleia
2. WHEN o protocolo é enviado THEN o sistema SHALL encaminhar para presidente da Comissão de Honrarias
3. WHEN a presidente aprova THEN o sistema SHALL encaminhar para membro da jurisdição inserir taxas
4. WHEN taxas são definidas THEN o sistema SHALL retornar para assembleia com status "aguardando pagamento"
5. WHEN comprovante é anexado THEN o sistema SHALL encaminhar para aprovação final da jurisdição
6. WHEN protocolo é concluído THEN o sistema SHALL atualizar cadastro dos membros com data de recebimento

### Requirement 7 - Protocolos de Honrarias (Coração das Cores)

**User Story:** Como admin da assembleia, eu quero processar a honraria Coração das Cores, para que membros recebam esta distinção única.

#### Acceptance Criteria

1. WHEN um protocolo Coração das Cores é criado THEN o sistema SHALL exibir apenas membros que NÃO possuem esta honraria
2. WHEN membros são selecionados THEN o sistema SHALL seguir o mesmo fluxo de aprovação das honrarias
3. WHEN o protocolo é concluído THEN o sistema SHALL marcar os membros como possuidores da honraria
4. WHEN um membro já possui a honraria THEN o sistema SHALL impedir nova seleção
5. WHEN taxas são aplicadas THEN o sistema SHALL seguir o fluxo padrão de pagamento e aprovação

### Requirement 8 - Protocolos de Honrarias (Grande Cruz das Cores)

**User Story:** Como admin da assembleia, eu quero processar a honraria Grande Cruz das Cores, para que membros recebam esta distinção com controle de presença na cerimônia.

#### Acceptance Criteria

1. WHEN um protocolo Grande Cruz é criado THEN o sistema SHALL exibir apenas membros que NÃO possuem esta honraria
2. WHEN o protocolo é aprovado THEN o sistema SHALL permitir seleção de quem esteve presente na cerimônia
3. WHEN membros presentes são selecionados THEN o sistema SHALL salvar como "Recebeu na cerimônia"
4. WHEN membros não estiveram presentes THEN o sistema SHALL salvar como "Recebeu indicação mas não passou pela cerimônia"
5. WHEN o protocolo é concluído THEN o sistema SHALL atualizar status baseado na presença na cerimônia

### Requirement 9 - Protocolo de Afastamento

**User Story:** Como admin da assembleia, eu quero registrar afastamentos de membros, para que o status seja atualizado corretamente.

#### Acceptance Criteria

1. WHEN um protocolo de afastamento é criado THEN o sistema SHALL exibir apenas membros ativos da assembleia
2. WHEN um membro é selecionado THEN o sistema SHALL exigir data do afastamento
3. WHEN o protocolo é aprovado THEN o sistema SHALL marcar o membro como inativo
4. WHEN o protocolo é rejeitado THEN o sistema SHALL exigir feedback para o admin
5. WHEN a data é inserida THEN o sistema SHALL validar que não é futura

### Requirement 10 - Protocolo de Novos Cargos Assembleia

**User Story:** Como admin da assembleia, eu quero atribuir cargos da assembleia, para que responsabilidades sejam distribuídas entre meninas ativas.

#### Acceptance Criteria

1. WHEN um protocolo de cargos assembleia é criado THEN o sistema SHALL exibir todos os cargos disponíveis
2. WHEN um cargo é preenchido THEN o sistema SHALL permitir seleção apenas de meninas ativas da assembleia
3. WHEN o protocolo é aprovado THEN o sistema SHALL atualizar todos os cargos simultaneamente
4. WHEN cargos são atualizados THEN o sistema SHALL garantir que apenas 1 pessoa ocupe cada cargo
5. WHEN novos cargos são definidos THEN o sistema SHALL remover cargos anteriores automaticamente

### Requirement 11 - Protocolo de Novos Cargos Conselho Consultivo

**User Story:** Como admin da assembleia, eu quero atribuir cargos do conselho consultivo, para que a governança seja estabelecida adequadamente.

#### Acceptance Criteria

1. WHEN um protocolo de cargos conselho é criado THEN o sistema SHALL exibir cargos do conselho consultivo
2. WHEN membros são selecionados THEN o sistema SHALL validar elegibilidade por tipo de usuário
3. WHEN "Presidente do Conselho" é preenchido THEN o sistema SHALL verificar que é Tio Maçom Mestre
4. WHEN o protocolo é aprovado THEN o sistema SHALL atualizar cargos e permissões automaticamente
5. WHEN cargos executivos são atribuídos THEN o sistema SHALL conceder acesso de Admin Assembleia

### Requirement 12 - Sistema de Logs e Auditoria

**User Story:** Como usuário do sistema, eu quero visualizar o histórico de alterações nos protocolos, para que haja transparência e rastreabilidade.

#### Acceptance Criteria

1. WHEN qualquer ação é realizada em um protocolo THEN o sistema SHALL registrar no log
2. WHEN um log é criado THEN o sistema SHALL incluir usuário, ação, data e hora
3. WHEN um protocolo é visualizado THEN o sistema SHALL exibir logs no final da tela
4. WHEN logs são exibidos THEN o sistema SHALL mostrar histórico completo de alterações
5. WHEN uma ação é registrada THEN o sistema SHALL atualizar logs automaticamente

### Requirement 13 - Interface de Gestão de Protocolos

**User Story:** Como usuário do sistema, eu quero uma interface intuitiva para gerenciar protocolos, para que o processo seja eficiente e claro.

#### Acceptance Criteria

1. WHEN um usuário acessa protocolos THEN o sistema SHALL exibir lista de protocolos existentes
2. WHEN um protocolo é criado THEN o sistema SHALL permitir seleção do tipo apropriado
3. WHEN dados são inseridos THEN o sistema SHALL validar campos obrigatórios
4. WHEN arquivos são anexados THEN o sistema SHALL aceitar imagens e PDFs
5. WHEN status muda THEN o sistema SHALL atualizar interface automaticamente
6. WHEN aprovações são necessárias THEN o sistema SHALL encaminhar para usuário apropriado