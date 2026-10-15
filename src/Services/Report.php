<?php

namespace mateusfbi\TotvsRmSoap\Services;

use \DOMDocument;

use InvalidArgumentException;

/**
 * Classe Report
 *
 * Responsável por gerar e manipular o XML de parâmetros dos relatórios a serem enviados via serviço SOAP.
 *
 * Essa classe permite:
 * - Configurar dados como coligada, id, filtros, parâmetros XML, nome do arquivo e contexto.
 * - Gerar o XML de parâmetros dos relatórios a partir de um array de itens.
 * - Invocar os métodos do serviço SOAP para realizar operações relacionadas a relatórios, como:
 *   - Gerar o relatório.
 *   - Obter a lista de relatórios.
 *   - Obter status, metadados, informações, tamanho, hash e chunks do arquivo gerado.
 *
 * @package mateusfbi\TotvsRmSoap\Services
 */

class Report extends AbstractService
{
    protected string $endpointPath = '/wsReport/MEX?wsdl';
    private int $coligada;
    private int $id;
    private string $filtro;
    private string $parametros;
    private string $nomeArquivo;
    private string $contexto;
    private int $offset;
    private int $length;
    private string $guid;

    /**
     * Define o código da coligada.
     *
     * @param int $coligada Código da coligada.
     * @return void
     */
    public function setColigada(int $coligada): static
    {
        $this->coligada = $coligada;
        return $this;
    }

    /**
     * Define o identificador do relatório.
     *
     * @param int $id Identificador do relatório.
     * @return void
     */
    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Define o nome do arquivo do relatório.
     *
     * @param string $nomeArquivo Nome do arquivo.
     * @return void
     */
    public function setNomeArquivo(string $nomeArquivo): static
    {
        $this->nomeArquivo = $nomeArquivo;
        return $this;
    }

    /**
     * Define o contexto da requisição.
     *
     * @param string $contexto Contexto da requisição.
     * @return void
     */
    public function setContexto(string $contexto): static
    {
        $this->contexto = $contexto;
        return $this;
    }

    /**
     * Define o GUID utilizado para obter informações do arquivo gerado.
     *
     * @param string $guid Identificador único (GUID).
     * @return void
     */
    public function setGuid(string $guid): static
    {
        $this->guid = $guid;
        return $this;
    }

    /**
     * Define o tamanho do chunk de arquivo a ser recuperado.
     *
     * @param int $length Tamanho do chunk.
     * @return void
     */
    public function setLength(int $length): static
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Define o offset para a requisição de um chunk do arquivo.
     *
     * @param int $offset Offset do chunk.
     * @return void
     */
    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

   	 /**
     * Gera o XML de filtros do relatório.
     *
     * Este método percorre um array de itens e monta a estrutura XML
     * que será enviada ao serviço SOAP.
     *
     * @param array[] $filtros Array de filtros. Cada filtro deve ser um array associativo com a seguinte estrutura:
     * [
     * 'bandname' => (string) Nome da banda do relatório.
     * 'mainfilter' => (bool) Se é o filtro principal.
     * 'filtersbytable' => (array[]) Lista de sub-filtros, onde cada um é um array com:
     * [
     * 'filter' => (string) A condição de filtro SQL.
     * 'name' => (string) Nome do filtro (opcional).
     * 'tablename' => (string) Nome da tabela alvo.
     * ]
     * ]
     * @return void
     */
    public function setFiltro(array $filtros = []): static
    {
		// 1. Inicializa o objeto DOMDocument
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true; // Formata o XML para melhor legibilidade
		$dom->preserveWhiteSpace = false;

		// 2. Cria o elemento raiz <ArrayOfRptFilterReportPar> com seus namespaces
		$root = $dom->createElement('ArrayOfRptFilterReportPar');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:i', 'http://www.w3.org/2001/XMLSchema-instance');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'http://www.totvs.com.br/RM/');
		$dom->appendChild($root);

		// 3. Percorre o array de entrada para criar cada bloco do XML
		foreach ($filtros as $index => $filtroData) {
		    if (!is_array($filtroData) || !isset($filtroData['bandname'], $filtroData['mainfilter'])) {
		        throw new InvalidArgumentException(
		            sprintf("Report: filtro #%d deve conter 'bandname' e 'mainfilter'.", $index)
		        );
		    }
		    $rptFilterReportPar = $dom->createElement('RptFilterReportPar');

		    // Adiciona a tag <BandName> (createTextNode escapa &<>)
		    self::appendTextElement($dom, $rptFilterReportPar, 'BandName', (string) $filtroData['bandname']);

		    // Cria a tag <FiltersByTable> e seus filhos
		    $filtersByTable = $dom->createElement('FiltersByTable');
		    $valueContent = ''; // Inicializa o conteúdo para a tag <Value>

		    if (!empty($filtroData['filtersbytable'])) {
		        if (!is_array($filtroData['filtersbytable'])) {
		            throw new InvalidArgumentException("Report: 'filtersbytable' deve ser um array.");
		        }
		        $allFilters = [];
		        foreach ($filtroData['filtersbytable'] as $tableFilter) {
		            if (!is_array($tableFilter) || !isset($tableFilter['filter'], $tableFilter['tablename'])) {
		                throw new InvalidArgumentException("Report: cada sub-filtro deve conter 'filter' e 'tablename'.");
		            }
		            $rptFilterByTablePar = $dom->createElement('RptFilterByTablePar');

		            $filterElement = $dom->createElement('Filter');
		            // Usa CDATA para o conteúdo do filtro
		            $filterElement->appendChild($dom->createCDATASection((string) $tableFilter['filter']));
		            $rptFilterByTablePar->appendChild($filterElement);

		            self::appendTextElement($dom, $rptFilterByTablePar, 'Name', (string) ($tableFilter['name'] ?? ''));
		            self::appendTextElement($dom, $rptFilterByTablePar, 'TableName', (string) $tableFilter['tablename']);

		            $filtersByTable->appendChild($rptFilterByTablePar);

		            // Acumula os filtros para usar na tag <Value>
		            $allFilters[] = (string) $tableFilter['filter'];
		        }
		        // Constrói o conteúdo da tag <Value>
		        $valueContent = '(' . implode(' AND ', $allFilters) . ')';
		    }
		    $rptFilterReportPar->appendChild($filtersByTable);

		    // Adiciona a tag <MainFilter>
		    self::appendTextElement($dom, $rptFilterReportPar, 'MainFilter', $filtroData['mainfilter'] ? 'true' : 'false');

		    // Adiciona a tag <Value> com o conteúdo gerado
		    $valueElement = $dom->createElement('Value');
		    if ($valueContent !== '') {
		        $valueElement->appendChild($dom->createCDATASection($valueContent));
		    }
		    $rptFilterReportPar->appendChild($valueElement);

		    // Adiciona o bloco completo ao elemento raiz
		    $root->appendChild($rptFilterReportPar);
		}

		// 4. Retorna a string XML completa
        $xml = $dom->saveXML();
        if ($xml === false) {
            throw new InvalidArgumentException('Report: falha ao gerar o XML de filtros.');
        }
        $this->filtro = $xml;
        return $this;
    }

	/**
     * Gera o XML de parâmetros do relatório usando DOMDocument para maior robustez.
     *
     * Este método percorre um array de itens e monta a estrutura XML
     * que será enviada ao serviço SOAP. Cada item do array deve conter as chaves:
     * - Description
     * - ParamName
     * - Type (String, Int16, Int32 ou DateTime)
     * - Value
     *
     * @param array $params Array contendo os dados necessários para gerar o XML.
     * @return void
     */
    public function setParametros(array $params = []): static
    {
        // 1. Inicializa o DOMDocument, a ferramenta correta para XML complexo.
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // Formata a saída para ser legível.
        $dom->preserveWhiteSpace = false;

        // 2. Define os URIs dos namespaces para reutilização e legibilidade.
        $nsRM = 'http://www.totvs.com.br/RM/';
        $nsI = 'http://www.w3.org/2001/XMLSchema-instance';
        $nsSystem = 'http://schemas.datacontract.org/2004/07/System';
        $nsMscorlib = '-mscorlib, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089-System-System.RuntimeType';
        $nsUnityHolder = '-mscorlib, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089-System-System.UnitySerializationHolder';
        $nsSerialization = 'http://schemas.microsoft.com/2003/10/Serialization/';
        $nsSchema = 'http://www.w3.org/2001/XMLSchema';

        // 3. Cria o elemento raiz com seu namespace padrão.
        $root = $dom->createElementNS($nsRM, 'ArrayOfRptParameterReportPar');
        // Adiciona os namespaces ao elemento raiz de forma explícita e segura.
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:i', $nsI);
        $dom->appendChild($root);

        // Percorre o array de parâmetros
        foreach ($params as $index => $item) {
            if (!is_array($item) || !isset($item['Description'], $item['ParamName'], $item['Type'], $item['Value'])) {
                throw new InvalidArgumentException(
                    sprintf("Report: parâmetro #%d deve conter 'Description', 'ParamName', 'Type' e 'Value'.", $index)
                );
            }
            // Cria o nó principal <RptParameterReportPar> (dentro do namespace padrão $nsRM)
            $node = $dom->createElement('RptParameterReportPar');

            self::appendTextElement($dom, $node, 'Description', (string) $item['Description']);
            self::appendTextElement($dom, $node, 'ParamName', (string) $item['ParamName']);

            // --- Bloco <Type> ---
            $typeNode = $dom->createElement('Type');
            // Adiciona os atributos com seus prefixos e namespaces de forma correta
            $typeNode->setAttributeNS($nsI, 'i:type', 'd3p2:RuntimeType');
            $typeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d3p1', $nsSystem);
            $typeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d3p2', $nsMscorlib);
            $typeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d3p3', $nsUnityHolder);
            $typeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:z', $nsSerialization);
            $typeNode->setAttributeNS($nsSerialization, 'z:FactoryType', 'd3p3:UnitySerializationHolder');

            // Sub-elementos de <Type>
            $typeValue = '';
            $valueType = '';
            switch ($item['Type']) {
                case 'String': $typeValue = 'System.String'; $valueType = 'string'; break;
                case 'Int16': $typeValue = 'System.Int16'; $valueType = 'int'; break;
                case 'Int32': $typeValue = 'System.Int32'; $valueType = 'int'; break;
                case 'DateTime':
                    $typeValue = 'System.DateTime';
                    $valueType = 'dateTime';
                    self::assertDateTime((string) $item['Value']);
                    break;
                default:
                    throw new InvalidArgumentException(
                        sprintf("Report: Type inválido '%s'. Use String, Int16, Int32 ou DateTime.", (string) $item['Type'])
                    );
            }

            // Para os filhos de <Type>, o namespace padrão é resetado (xmlns="").
            // Para isso, criamos os elementos sem namespace explícito.
            $dataNode = $dom->createElement('Data');
            $dataNode->appendChild($dom->createTextNode($typeValue));
            $dataNode->setAttributeNS($nsI, 'i:type', 'd4p1:string');
            $dataNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d4p1', $nsSchema);
            $typeNode->appendChild($dataNode);

            $unityTypeNode = $dom->createElement('UnityType');
            $unityTypeNode->appendChild($dom->createTextNode('4'));
            $unityTypeNode->setAttributeNS($nsI, 'i:type', 'd4p1:int');
            $unityTypeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d4p1', $nsSchema);
            $typeNode->appendChild($unityTypeNode);

            $assemblyNameNode = $dom->createElement('AssemblyName');
            $assemblyNameNode->appendChild($dom->createTextNode('mscorlib, Version=4.0.0.0, Culture=neutral, PublicKeyToken=b77a5c561934e089'));
            $assemblyNameNode->setAttributeNS($nsI, 'i:type', 'd4p1:string');
            $assemblyNameNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d4p1', $nsSchema);
            $typeNode->appendChild($assemblyNameNode);

            $node->appendChild($typeNode); // Adiciona o bloco <Type> completo

            // --- Bloco <Value> ---
            $valueNode = $dom->createElement('Value');
            $valueNode->appendChild($dom->createTextNode((string) $item['Value']));
            $valueNode->setAttributeNS($nsI, 'i:type', 'd3p1:' . $valueType);
            $valueNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d3p1', $nsSchema);
            $node->appendChild($valueNode);

            self::appendTextElement($dom, $node, 'Visible', 'true');

            $root->appendChild($node); // Adiciona o nó do parâmetro ao XML raiz
        }

        // 4. Salva o XML gerado na propriedade da classe.
        // O saveXML() do DOM já gera o XML completo e formatado.
        $xml = $dom->saveXML();
        if ($xml === false) {
            throw new InvalidArgumentException('Report: falha ao gerar o XML de parâmetros.');
        }
        $this->parametros = $xml;
        return $this;
    }

    /**
     * Obtém a lista de relatórios disponíveis.
     *
     * Executa o método GetReportList do serviço SOAP e processa o resultado,
     * retornando em formato de array associativo.
     *
     * @return array Lista de relatórios com campos como coligada, sistema, id, código, nome, data e uuid.
     */
    public function getReportList(): array
    {
        $this->requireSet('coligada', 'setColigada');
        $rawResult = $this->callWebServiceMethod('GetReportList', ['codColigada' => $this->coligada]);

        return self::parseReportList($rawResult);
    }

    /**
     * Converte a string de retorno do GetReportList em array estruturado.
     *
     * Extraído para dentro da classe para permitir teste unitário sem SOAP.
     * Registros malformados são ignorados (sem I/O lateral na biblioteca).
     */
    public static function parseReportList(mixed $rawResult): array
    {
        $return = [];

        if (empty($rawResult) || !is_string($rawResult)) {
            return [];
        }

        // Desembrulha string serializada do PHP (ex: s:7625:"...";), preservando o conteúdo.
        $result = $rawResult;
        if (preg_match('/^s:\d+:"(.*)"\;?$/s', $result, $m)) {
            $result = $m[1];
        } else {
            $result = (string) preg_replace('/^s:\d+:"/', '', $result);
            $result = (string) preg_replace('/"\;?$/', '', $result);
        }

        $registros = explode(';,', $result);

        foreach ($registros as $registro) {
            $registro = trim(trim($registro), '";');
            if ($registro === '') {
                continue;
            }

            $campos = explode(',', $registro);

            // Garante que há campos suficientes antes de tentar acessá-los
            if (count($campos) < 6) {
                // Registros malformados são ignorados (sem I/O lateral na biblioteca).
                continue;
            }
            // coligada, sistema, id, codigo, nome (pelo menos 1 parte), data, uuid
            $return[] = [
                'coligada' => trim($campos[0]),
                'sistema' => trim($campos[1]),
                'id' => trim($campos[2]),
                'codigo' => trim($campos[3]),
                // O nome pode conter vírgulas, então pegamos tudo entre o 4º campo e os dois últimos
                'nome' => trim(implode(',', array_slice($campos, 4, -2))),
                'data' => trim($campos[count($campos) - 2]),
                'uuid' => trim($campos[count($campos) - 1], '"; '),
            ];
        }

        return $return;
    }

    /**
     * Gera o relatório através do serviço SOAP.
     *
     * Envia a requisição com os parâmetros configurados (coligada, id, filtro, parâmetros, nome do arquivo e contexto)
     * e retorna o resultado da execução.
     *
     * @return string Resultado da geração do relatório.
     */
    public function generateReport(): string
    {
        $this->requireSet('coligada', 'setColigada');
        $this->requireSet('id', 'setId');
        $this->requireSet('nomeArquivo', 'setNomeArquivo');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'codColigada' => $this->coligada,
            'id'          => $this->id,
            'filters'     => $this->filtro ?? null,
            'parameters'  => $this->parametros ?? null,
            'fileName'    => $this->nomeArquivo,
            'contexto'    => $this->contexto,
        ];
        return $this->callWebServiceMethod('GenerateReport', $params);
    }

    /**
     * Gera o relatório de forma assíncrona através do serviço SOAP.
     *
     * Envia os mesmos parâmetros do método generateReport(), porém utilizando o método assíncrono do endpoint.
     *
     * @return string Resultado retornado pela execução assíncrona do relatório.
     */
    public function generateReportAsynchronous(): string
    {
        $this->requireSet('coligada', 'setColigada');
        $this->requireSet('id', 'setId');
        $this->requireSet('nomeArquivo', 'setNomeArquivo');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'codColigada' => $this->coligada,
            'id'          => $this->id,
            'filters'     => $this->filtro ?? null,
            'parameters'  => $this->parametros ?? null,
            'fileName'    => $this->nomeArquivo,
            'contexto'    => $this->contexto,
        ];
        return $this->callWebServiceMethod('GenerateReportAsynchronous', $params);
    }

   /**
     * Obtém os metadados do relatório.
     *
     * Envia uma requisição SOAP para recuperar os metadados do relatório com base nos parâmetros coligada e id.
     *
     * @return string Metadados do relatório em formato de string.
     */
    public function getReportMetaData(): string
    {
        $this->requireSet('coligada', 'setColigada');
        $this->requireSet('id', 'setId');
        $params = [
            'codColigada' => $this->coligada,
            'id'      => $this->id
        ];
        return $this->callWebServiceMethod('GetReportMetaData', $params);
    }

    /**
     * Obtém informações adicionais do relatório.
     *
     * Executa uma requisição SOAP para recuperar informações do relatório baseado no idReport e retorna
     * o resultado como um array.
     *
     * @return array Informações do relatório ou array vazio em caso de falha.
     */
    public function getReportInfo(): array
    {
        $this->requireSet('coligada', 'setColigada');
        $this->requireSet('id', 'setId');
        $params = [
            'codColigada'      => $this->coligada,
            'idReport'      => $this->id
        ];
        $result = $this->callWebServiceMethod('GetReportInfo', $params);
        return isset($result->string) ? $result->string : [];
    }

    /**
     * Obtém o status do relatório gerado.
     *
     * Envia uma requisição SOAP usando o guid para recuperar
     * o status do relatório gerado pelo generateReportAsynchronous.
     *
     * @return string Status do relatório.
     */
    public function getGeneratedReportStatus(): string
    {
        $this->requireSet('guid', 'setGuid');
        $params = [
            'id'      => $this->guid
        ];
        return $this->callWebServiceMethod('GetGeneratedReportStatus', $params);
    }

    /**
     * Obtém o tamanho do arquivo do relatório gerado.
     *
     * Envia uma requisição SOAP utilizando o GUID para recuperar o tamanho do arquivo.
     *
     * @return int Tamanho do arquivo do relatório.
     */
    public function getGeneratedReportSize(): int
    {
        $this->requireSet('guid', 'setGuid');
        $params = [
            'guid'      => $this->guid
        ];
        return (int) $this->callWebServiceMethod('GetGeneratedReportSize', $params);
    }

    /**
     * Obtém o hash do arquivo do relatório.
     *
     * Envia uma requisição SOAP utilizando o GUID para recuperar o hash do arquivo.
     *
     * @return string Hash do arquivo.
     */
    public function getFileHash(): string
    {
        $this->requireSet('guid', 'setGuid');
        $params = [
            'guid'      => $this->guid
        ];
        return $this->callWebServiceMethod('GetFileHash', $params);
    }

    /**
     * Obtém um chunk (pedaço) do arquivo do relatório.
     *
     * Envia uma requisição SOAP utilizando o GUID, offset e length para recuperar um pedaço do arquivo.
     *
     * @return string Chunk do arquivo do relatório.
     */
    public function getFileChunk(): string
    {
        $this->requireSet('guid', 'setGuid');
        $this->requireSet('offset', 'setOffset');
        $this->requireSet('length', 'setLength');
        $params = [
            'guid'      => $this->guid,
            'offset'      => $this->offset,
            'length'      => $this->length
        ];
        return $this->callWebServiceMethod('GetFileChunk', $params);
    }

    private function requireSet(string $property, string $setter): void
    {
        if (!isset($this->$property)) {
            throw new InvalidArgumentException(
                sprintf('Report: chame %s() antes de executar a operação.', $setter)
            );
        }
    }

    /**
     * Cria <name>valor</name> com escaping correto (&<>).
     */
    private static function appendTextElement(DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $parent->appendChild($dom->createElement($name))
            ->appendChild($dom->createTextNode($value));
    }

    private static function assertDateTime(string $value): void
    {
        if ($value === '' || date_create($value) === false) {
            throw new InvalidArgumentException(
                sprintf("Report: Value DateTime inválido '%s'. Use formato ISO 8601 (ex.: 2026-05-31T00:00:00).", $value)
            );
        }
    }
}
