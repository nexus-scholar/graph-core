<?php

namespace Mbsoft\Graph\IO;

use DOMDocument;
use DOMException;
use Mbsoft\Graph\Contracts\ExporterInterface;
use Mbsoft\Graph\Contracts\GraphInterface;
use Mbsoft\Graph\IO\Concerns\CollectsAttributes;

/**
 * Exports a graph to GEXF XML format.
 */
final class GexfExporter implements ExporterInterface
{
    use CollectsAttributes;

    /**
     * @param GraphInterface $g
     *
     * @return string
     *
     * @throws DOMException
     */
    public function export(GraphInterface $g): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Create root gexf element
        $gexf = $dom->createElementNS('http://www.gexf.net/1.3', 'gexf');
        $gexf->setAttribute('version', '1.3');
        $gexf->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $gexf->setAttribute(
            'xsi:schemaLocation',
            'http://www.gexf.net/1.3 http://www.gexf.net/1.3/gexf.xsd',
        );
        $dom->appendChild($gexf);

        // Add meta information
        $meta = $dom->createElement('meta');
        $meta->setAttribute('lastmodifieddate', date('Y-m-d'));
        $creator = $dom->createElement('creator', 'nexus-scholar/graph-core');
        $meta->appendChild($creator);
        $gexf->appendChild($meta);

        // Create graph element
        $graph = $dom->createElement('graph');
        $graph->setAttribute('mode', 'static');
        $graph->setAttribute('defaultedgetype', $g->isDirected() ? 'directed' : 'undirected');
        $gexf->appendChild($graph);

        // Collect attribute keys
        $nodeAttrKeys = $this->collectAttributeKeys($g, 'node');
        $edgeAttrKeys = $this->collectAttributeKeys($g, 'edge');

        // Define node attributes if any exist
        if (!empty($nodeAttrKeys)) {
            $nodeAttrs = $dom->createElement('attributes');
            $nodeAttrs->setAttribute('class', 'node');

            foreach ($nodeAttrKeys as $index => $attrName) {
                $attr = $dom->createElement('attribute');
                $attr->setAttribute('id', (string) $index);
                $attr->setAttribute('title', $attrName);
                $attr->setAttribute('type', 'string');
                $nodeAttrs->appendChild($attr);
            }

            $graph->appendChild($nodeAttrs);
        }

        // Define edge attributes if any exist
        if (!empty($edgeAttrKeys)) {
            $edgeAttrs = $dom->createElement('attributes');
            $edgeAttrs->setAttribute('class', 'edge');

            foreach ($edgeAttrKeys as $index => $attrName) {
                $attr = $dom->createElement('attribute');
                $attr->setAttribute('id', (string) $index);
                $attr->setAttribute('title', $attrName);
                $attr->setAttribute('type', 'string');
                $edgeAttrs->appendChild($attr);
            }

            $graph->appendChild($edgeAttrs);
        }

        // Add nodes
        $nodes = $dom->createElement('nodes');
        foreach ($g->nodes() as $nodeId) {
            $node = $dom->createElement('node');
            $node->setAttribute('id', $nodeId);
            $node->setAttribute('label', $nodeId);

            $nodeAttrsData = $g->nodeAttrs($nodeId);
            if (!empty($nodeAttrsData)) {
                $attvalues = $dom->createElement('attvalues');
                foreach ($nodeAttrsData as $attrName => $attrValue) {
                    $index = array_search($attrName, $nodeAttrKeys);
                    if ($index !== false) {
                        $attvalue = $dom->createElement('attvalue');
                        $attvalue->setAttribute('for', (string) $index);
                        $attvalue->setAttribute('value', (string) $attrValue);
                        $attvalues->appendChild($attvalue);
                    }
                }
                $node->appendChild($attvalues);
            }

            $nodes->appendChild($node);
        }
        $graph->appendChild($nodes);

        // Add edges
        $edges = $dom->createElement('edges');
        $edgeId = 0;
        foreach ($g->edges() as $edge) {
            $edgeElement = $dom->createElement('edge');
            $edgeElement->setAttribute('id', (string) $edgeId++);
            $edgeElement->setAttribute('source', $edge->from);
            $edgeElement->setAttribute('target', $edge->to);

            if (!empty($edge->attributes)) {
                $attvalues = $dom->createElement('attvalues');
                foreach ($edge->attributes as $attrName => $attrValue) {
                    $index = array_search($attrName, $edgeAttrKeys);
                    if ($index !== false) {
                        $attvalue = $dom->createElement('attvalue');
                        $attvalue->setAttribute('for', (string) $index);
                        $attvalue->setAttribute('value', (string) $attrValue);
                        $attvalues->appendChild($attvalue);
                    }
                }
                $edgeElement->appendChild($attvalues);
            }

            $edges->appendChild($edgeElement);
        }
        $graph->appendChild($edges);

        /** @var string $xml */
        $xml = $dom->saveXML();

        if (!$xml) {
            throw new DOMException('Failed to generate XML from DOMDocument');
        }

        return $xml;
    }
}
