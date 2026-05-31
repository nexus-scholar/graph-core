# nexus-scholar/graph-core

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/nexus-scholar/graph-core.svg?style=flat-square)](https://packagist.org/packages/nexus-scholar/graph-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nexus-scholar/graph-core/ci.yml?branch=main&style=flat-square)](https://github.com/nexus-scholar/graph-core/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/nexus-scholar/graph-core.svg?style=flat-square)](https://packagist.org/packages/nexus-scholar/graph-core)

`graph-core` is a lightweight PHP graph data-structure package for directed and undirected graphs. It provides node and edge attributes, efficient adjacency lookups, subgraph views, and exporters for common graph-visualization formats.

## Role In Nexus Scholar

This package is the graph foundation for Nexus Scholar citation-network work. It intentionally stays generic: scholarly concepts such as papers, citations, co-citation, bibliographic coupling, screening, and exports live in `nexus-scholar/core`, while graph storage, traversal primitives, attributes, and serialization live here.

Use it directly for PHP graph modeling, or pair it with `nexus-scholar/graph-algorithms` when you need centrality, pathfinding, traversal, components, ordering, or minimum-spanning-tree algorithms.

## Features

- Directed and undirected graph support.
- Node and edge attributes.
- Integer indexing internally for fast adjacency lookups.
- Read-only subgraph views without copying the full graph.
- Cytoscape.js JSON, GraphML, and GEXF exporters.
- PHP 8.2+ types and interfaces.
- No runtime dependencies except `ext-dom` for XML export formats.
- Pest-backed test suite.

## Requirements

- PHP 8.2+
- `ext-dom` for GraphML and GEXF exports

## Installation

```bash
composer require nexus-scholar/graph-core
```

## Quick Start

```php
use Mbsoft\Graph\Domain\Graph;

$graph = new Graph(directed: true);

$graph->addNode('A', ['label' => 'Node A']);
$graph->addNode('B', ['label' => 'Node B']);
$graph->addNode('C', ['label' => 'Node C']);

$graph->addEdge('A', 'B', ['weight' => 1.5]);
$graph->addEdge('B', 'C', ['weight' => 2.0]);
$graph->addEdge('C', 'A', ['weight' => 0.5]);

count($graph->nodes()); // 3
count($graph->edges()); // 3

if ($graph->hasEdge('A', 'B')) {
    $weight = $graph->edgeAttrs('A', 'B')['weight'];
}

$successors = $graph->successors('A');
$predecessors = $graph->predecessors('C');
```

## Undirected Graphs

```php
use Mbsoft\Graph\Domain\Graph;

$graph = new Graph(directed: false);

$graph->addEdge('A', 'B', ['type' => 'friendship']);
$graph->addEdge('B', 'C', ['type' => 'friendship']);

$graph->hasEdge('A', 'B'); // true
$graph->hasEdge('B', 'A'); // true

$graph->successors('B');
$graph->predecessors('B');
```

## Edge Lists

```php
use Mbsoft\Graph\Domain\Graph;

$edges = [
    ['A', 'B', ['weight' => 1.0]],
    ['B', 'C', ['weight' => 2.0]],
    ['C', 'D', ['weight' => 1.5]],
];

$graph = Graph::fromEdgeList($edges, directed: true);
```

## Subgraph Views

```php
use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\Domain\SubgraphView;

$graph = new Graph();
$graph->addEdge('A', 'B');
$graph->addEdge('B', 'C');
$graph->addEdge('C', 'D');

$subgraph = new SubgraphView($graph, ['A', 'B', 'C']);

$subgraph->nodes();
$subgraph->edges();
$subgraph->hasEdge('C', 'D'); // false
```

## Export Formats

Export for browser visualization with Cytoscape.js:

```php
use Mbsoft\Graph\IO\CytoscapeJsonExporter;

$exporter = new CytoscapeJsonExporter();
$json = $exporter->export($graph);
```

Export GraphML for tools such as Gephi, yEd, or NetworkX:

```php
use Mbsoft\Graph\IO\GraphMLExporter;

$exporter = new GraphMLExporter();
$xml = $exporter->export($graph);
```

Export GEXF for Gephi and compatible network-analysis tools:

```php
use Mbsoft\Graph\IO\GexfExporter;

$exporter = new GexfExporter();
$xml = $exporter->export($graph);
```

## Architecture

Core interfaces and classes include:

- `GraphInterface`: read-only graph operations.
- `MutableGraphInterface`: graph operations with mutation methods.
- `ExporterInterface`: common export contract.
- `Graph`: mutable graph implementation.
- `SubgraphView`: efficient filtered graph view.
- `Node` and `Edge`: value objects for graph elements.
- `IndexMap`: bidirectional mapping between external node IDs and internal integer indexes.

## Testing

```bash
composer test
composer test:coverage
composer analyse
```

## Use Cases

- Citation and bibliographic networks.
- Knowledge graphs and concept maps.
- Dependency graphs.
- Workflow and state-machine modeling.
- Data visualization exports.
- Route, logistics, and infrastructure graphs.

## See Also

- [nexus-scholar/graph-algorithms](https://github.com/nexus-scholar/graph-algorithms) - algorithms for centrality, pathfinding, traversal, components, ordering, and minimum spanning trees.
- [nexus-scholar/core](https://github.com/nexus-scholar/core) - Nexus Scholar workflow engine that uses graph packages for citation-network features.
- [nexus-scholar/scholar-graph](https://github.com/nexus-scholar/scholar-graph) - legacy scholarly graph prototype that preceded the current graph packages.

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).
