# mod_agreement - Termo de concordância

Atividade Moodle para publicar um termo e exigir que o participante registre explicitamente uma decisão: **Concordo** ou **Não concordo**.

O foco é auditoria simples e objetiva. O plugin não trata a ação como mera confirmação de leitura.

## Recursos

- resposta explícita “Concordo” / “Não concordo”;
- permissão de responder separada da permissão de apenas visualizar o termo;
- uma resposta imutável por participante e por versão do termo;
- alteração do texto gera automaticamente uma nova versão;
- respostas antigas permanecem vinculadas à versão original;
- registro de data e hora da decisão;
- hash SHA-256 de cada versão do conteúdo;
- relatório do professor com situação da versão atual, histórico de versões e histórico de respostas;
- suporte ao sistema de privacidade do Moodle;
- backup e restauração da atividade, incluindo histórico quando dados de usuários são incluídos;
- interface em `en` e `pt_br`;
- templates Mustache, sem renderer customizado.

## Comportamento de versionamento

Alterar apenas o nome ou a introdução da atividade não cria uma nova versão. Alterar o texto do termo ou o formato do texto cria a versão seguinte.

Quando uma nova versão é publicada, o participante precisa responder novamente. A resposta da versão anterior não é sobrescrita.

## Instalação

Copie a pasta `agreement` para:

```text
mod/agreement
```

No Moodle 5.1+, considerando a estrutura com `public/`, o caminho físico normalmente será:

```text
public/mod/agreement
```

Depois acesse **Administração do site > Notificações** para concluir a instalação.

## Compatibilidade

- Moodle 4.5 a 5.2
- PHP conforme os requisitos da versão do Moodle utilizada

## Licença

GNU GPL v3 ou posterior.
