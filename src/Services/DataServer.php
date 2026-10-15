<?php

namespace mateusfbi\TotvsRmSoap\Services;

use mateusfbi\TotvsRmSoap\Utils\Serialize;

use InvalidArgumentException;

/**
 * Classe DataServer
 *
 * Responsável por interagir com o DataServer da Totvs RM. Essa classe permite
 * realizar operações de persistência, leitura e exclusão de registros, além de montar
 * o XML necessário para algumas requisições.
 *
 * @package mateusfbi\TotvsRmSoap\Services
 */
class DataServer extends AbstractService
{
    protected string $endpointPath = '/wsDataServer/MEX?wsdl';
    private string $dataServer;
    private string $primaryKey;
    private string $contexto;
    private string $filtro;
    private string $xml;

    /**
     * Define o DataServer a ser utilizado.
     *
     * @param string $dataServer Nome do DataServer.
     * @return void
     */
    public function setDataServer(string $dataServer): void
    {
        $this->dataServer = $dataServer;
    }

    /**
     * Define a chave primária do registro.
     *
     * @param string $primaryKey Chave primária.
     * @return void
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Define o contexto da requisição.
     *
     * @param string $context Contexto da requisição.
     * @return void
     */
    public function setContexto(string $context): void
    {
        $this->contexto = $context;
    }

    /**
     * Define o filtro para a requisição (ex.: cláusulas WHERE, condições específicas).
     *
     * @param string $filtro Filtro a ser aplicado.
     * @return void
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    /**
     * Define diretamente o XML da requisição do DataServer.
     *
     * @param string $xml Conteúdo XML completo.
     * @return string XML definido.
     */
    public function setXML(string $xml): string
    {
        return $this->xml = $xml;
    }
    
    /**
     * Monta o XML da requisição do DataServer a partir de um array.
     *
     * Cria um documento XML com o nome da tabela informado e adiciona elementos para cada campo
     * contido no array $xmlArray.
     *
     * @param array $data Array associativo contendo os dados a serem convertidos em XML, estruturado com a chave 'root' para o elemento raiz e 'TABELA' para os dados.
     * @return void
     */
    public function setXMLFromArray(array $xmlArray): void
    {
        if (empty($xmlArray['root'])) {
            throw new \InvalidArgumentException('Root não informado');
        }

        self::assertValidTagName((string) $xmlArray['root']);

        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $root = $dom->createElement($xmlArray['root']);
        $dom->appendChild($root);

        if (!empty($xmlArray['TABELA']) && is_array($xmlArray['TABELA'])) {
            $this->appendTabela($dom, $root, $xmlArray['TABELA']);
        }

        $xml = $dom->saveXML();
        if ($xml === false) {
            throw new \InvalidArgumentException('DataServer: falha ao gerar o XML.');
        }

        $this->xml = $xml;
    }

    /**
     * Retorna o XML gerado pelo método setXML.
     *
     * @return string
     */
    public function getXML(): string
    {
        if (!isset($this->xml)) {
            throw new \InvalidArgumentException('DataServer: XML não definido. Use setXML() ou setXMLFromArray().');
        }

        return $this->xml;
    }

    private function appendTabela(
        \DOMDocument $dom,
        \DOMElement $parent,
        array $tabela
    ): void {
        foreach ($tabela as $tag => $conteudo) {
            self::assertValidTagName((string) $tag);

            // Nó repetível
            if (is_array($conteudo) && array_is_list($conteudo)) {
                foreach ($conteudo as $item) {
                    if (!is_array($item)) {
                        throw new \InvalidArgumentException(
                            sprintf("DataServer: item da tabela '%s' deve ser um array associativo.", $tag)
                        );
                    }
                    $node = $dom->createElement($tag);
                    $this->appendCampos($dom, $node, $item);
                    $parent->appendChild($node);
                }
                continue;
            }

            // Nó único
            if (is_array($conteudo)) {
                $node = $dom->createElement($tag);
                $this->appendCampos($dom, $node, $conteudo);
                $parent->appendChild($node);
            }
        }
    }

    private function appendCampos(
        \DOMDocument $dom,
        \DOMElement $parent,
        array $campos
    ): void {
        foreach ($campos as $campo => $valor) {
            self::assertValidTagName((string) $campo);
            if (is_array($valor) || is_object($valor)) {
                throw new \InvalidArgumentException(
                    sprintf("DataServer: valor do campo '%s' deve ser escalar, recebido %s.", $campo, get_debug_type($valor))
                );
            }
            // createTextNode escapa &<>, ao contrário de createElement($name, $value).
            $parent->appendChild($dom->createElement($campo))
                ->appendChild($dom->createTextNode((string) $valor));
        }
    }

    private static function assertValidTagName(string $tag): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_.-]*$/', $tag)) {
            throw new \InvalidArgumentException(
                sprintf("DataServer: nome de tag XML inválido '%s'.", $tag)
            );
        }
    }

    /**
     * Persiste um registro no DataServer.
     *
     * Envia a requisição SOAP para salvar um registro, utilizando o XML de dados e o contexto definidos.
     *
     * @return string Resultado da operação de salvamento, conforme retornado pelo serviço SOAP.
     */
    public function saveRecord(): string
    {
        $this->requireSet('dataServer', 'setDataServer');
        $this->requireSet('xml', 'setXML/setXMLFromArray');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'DataServerName'    => $this->dataServer,
            'XML'               => $this->xml,
            'Contexto'          => $this->contexto
        ];
        return $this->callWebServiceMethod('SaveRecord', $params);
    }

    /**
     * Lê um registro específico do DataServer, utilizando a chave primária definida.
     *
     * @return array Array associativo contendo os dados do registro lido.
     */
    public function readRecord(): array
    {
        $this->requireSet('dataServer', 'setDataServer');
        $this->requireSet('primaryKey', 'setPrimaryKey');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'DataServerName'    => $this->dataServer,
            'PrimaryKey'        => $this->primaryKey,
            'Contexto'          => $this->contexto
        ];
        $result = $this->callWebServiceMethod('ReadRecord', $params);
        return Serialize::result($result);
    }

    /**
     * Lê dados do DataServer utilizando um filtro específico.
     *
     * Envia uma requisição SOAP com o nome do DataServer, filtro e contexto para retornar
     * os dados que satisfazem as condições definidas.
     *
     * @return array Array associativo contendo os dados retornados.
     */
    public function readView(): array
    {
        $this->requireSet('dataServer', 'setDataServer');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'DataServerName'    => $this->dataServer,
            'Filtro'            => $this->filtro ?? '',
            'Contexto'          => $this->contexto
        ];
        $result = $this->callWebServiceMethod('ReadView', $params);
        return Serialize::result($result);
    }

    /**
     * Exclui um registro do DataServer utilizando um XML de dados.
     *
     * Envia uma requisição SOAP para remover um registro, com base no XML fornecido e no contexto.
     *
     * @return int Resultado da operação de exclusão conforme retornado pelo serviço SOAP.
     */
    public function deleteRecord(): int
    {
        $this->requireSet('dataServer', 'setDataServer');
        $this->requireSet('xml', 'setXML/setXMLFromArray');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'DataServerName'    => $this->dataServer,
            'XML'               => $this->xml,
            'Contexto'          => $this->contexto
        ];
        return (int) $this->callWebServiceMethod('DeleteRecord', $params);
    }

    /**
     * Exclui um registro do DataServer utilizando a chave primária.
     *
     * Envia a requisição SOAP para remover o registro identificado pela chave primária.
     *
     * @return array Array associativo com o resultado da operação de exclusão.
     */
    public function deleteRecordByKey(): array
    {
        $this->requireSet('dataServer', 'setDataServer');
        $this->requireSet('primaryKey', 'setPrimaryKey');
        $this->requireSet('contexto', 'setContexto');
        $params = [
            'DataServerName'    => $this->dataServer,
            'PrimaryKey'        => $this->primaryKey,
            'Contexto'          => $this->contexto,
        ];
        $result = $this->callWebServiceMethod('DeleteRecordByKey', $params);
        return Serialize::result($result);
    }

    private function requireSet(string $property, string $setter): void
    {
        if (!isset($this->$property)) {
            throw new InvalidArgumentException(
                sprintf('DataServer: chame %s() antes de executar a operação.', $setter)
            );
        }
    }


}
