# Agreement

Agreement é uma atividade Moodle para publicar um termo, política, declaração ou outro texto que exija de cada participante uma decisão explícita: **Concordo** ou **Não concordo**.

A atividade foi pensada para situações em que uma simples marcação de "visualizado" não é suficiente. A decisão do participante fica vinculada exatamente à versão do texto apresentada, portanto alterações posteriores não reescrevem o histórico.

## O que a atividade faz

- Registra uma resposta explícita **Concordo** ou **Não concordo**.
- Separa a permissão de responder da permissão de apenas visualizar o termo.
- Permite uma resposta imutável por participante e por versão do termo.
- Cria automaticamente uma nova versão quando o texto do termo ou seu formato é alterado.
- Mantém respostas anteriores vinculadas à versão original, sem sobrescrevê-las.
- Registra data e hora de cada decisão.
- Armazena um hash SHA-256 do conteúdo de cada versão.
- Fornece relatório para o professor com situação da versão atual, histórico de versões e histórico de respostas.
- Suporta as APIs de privacidade do Moodle.
- Suporta backup e restauração, incluindo histórico de versões e respostas quando os dados dos usuários são incluídos.
- Possui pacotes de idioma em inglês e português do Brasil.
- Usa templates Mustache e APIs do Moodle sem renderer customizado.

## Comportamento do versionamento

Alterar apenas o nome ou a introdução da atividade não cria uma nova versão. Alterar o texto do termo ou seu formato cria a versão seguinte.

Depois que uma nova versão é publicada, os participantes precisam responder novamente. A decisão anterior permanece preservada e vinculada à versão que foi apresentada originalmente.

## Fluxo do participante

O participante abre a atividade, lê o texto atual e registra uma das decisões disponíveis. Depois de registrada, essa resposta permanece como a decisão auditável daquele participante para aquela versão, em vez de ser substituída silenciosamente por alterações posteriores.

Se o professor publicar uma nova versão, o participante verá o novo texto e poderá registrar uma nova decisão específica para ela.

## Relatório do professor

Professores com a permissão adequada podem acompanhar a situação das respostas da versão atual e consultar versões e respostas anteriores. Assim é possível diferenciar quem ainda não respondeu ao texto vigente de quem respondeu apenas a uma versão antiga.

## Modelo de auditoria

O plugin mantém versões do termo e respostas em registros separados. Cada versão guarda seu texto, formato, informações de criação e hash do conteúdo, enquanto cada resposta referencia a versão que estava vigente quando a decisão foi registrada.

O modelo é propositalmente simples: registra qual versão do texto existia, quem respondeu, qual decisão foi registrada e quando isso aconteceu.
