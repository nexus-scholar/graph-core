# nexus-scholar/graph-core

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/nexus-scholar/graph-core.svg?style=flat-square)](https://packagist.org/packages/nexus-scholar/graph-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nexus-scholar/graph-core/ci.yml?branch=main&style=flat-square)](https://github.com/nexus-scholar/graph-core/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/nexus-scholar/graph-core.svg?style=flat-square)](https://packagist.org/packages/nexus-scholar/graph-core)


A lightweight, performant, and dependency-free graph data structure library for PHP. This library provides a clean, modern API for working with directed and undirected graphs, with support for node and edge attributes, subgraph views, and multiple export formats.

## ✨ Features

- 🚀 **High Performance**: Uses integer indexing internally for O(1) adjacency lookups
- 🎯 **Clean API**: Well-designed interfaces following SOLID principles
- 📊 **Directed & Undirected**: Full support for both graph types
- 🏷️ **Rich Attributes**: Store arbitrary data on nodes and edges
- 👁️ **Subgraph Views**: Create efficient filtered views without copying data
- 📤 **Multiple Export Formats**: Cytoscape.js JSON, GraphML, GEXF
- 🔒 **Type-Safe**: Leverages PHP 8.2+ features for type safety
- 📦 **Zero Dependencies**: No external dependencies (except ext-dom for XML exports)
- ✅ **Well-Tested**: Comprehensive test coverage with Pest

## 📋 Requirements

- PHP 8.2 or higher
- ext-dom (for XML export formats)

## 📦 Installation

Install via Composer:

```bash
composer require nexus-scholar/graph-core
```

## 🚀 Quick Start

### Basic Usage

```php
use Mbsoft\Graph\Domain\Graph;

// Create a directed graph
$graph = new Graph(directed: true);

// Add nodes with attributes
$graph->addNode('A', ['label' => 'Node A', 'color' => 'red']);
$graph->addNode('B', ['label' => 'Node B', 'color' => 'blue']);
$graph->addNode('C', ['label' => 'Node C', 'color' => 'green']);

// Add edges with weights
$graph->addEdge('A', 'B', ['weight' => 1.5]);
$graph->addEdge('B', 'C', ['weight' => 2.0]);
$graph->addEdge('C', 'A', ['weight' => 0.5]);

// Query the graph
echo count($graph->nodes()); // 3
echo count($graph->edges()); // 3

// Check connections
if ($graph->hasEdge('A', 'B')) {
    $weight = $graph->edgeAttrs('A', 'B')['weight'];
    echo "Edge A->B has weight: $weight\n";
}

// Get neighbors
$successors = $graph->successors('A');   // ['B']
$predecessors = $graph->predecessors('C'); // ['B']
```

### Undirected Graphs

```php
use Mbsoft\Graph\Domain\Graph;

// Create an undirected graph
$graph = new Graph(directed: false);

$graph->addEdge('A', 'B', ['type' => 'friendship']);
$graph->addEdge('B', 'C', ['type' => 'friendship']);

// In undirected graphs, edges work both ways
$graph->hasEdge('A', 'B'); // true
$graph->hasEdge('B', 'A'); // true (same edge)

// Successors and predecessors are the same (neighbors)
$graph->successors('B');   // ['A', 'C']
$graph->predecessors('B'); // ['A', 'C']
```

### Creating Graphs from Edge Lists

```php
use Mbsoft\Graph\Domain\Graph;

$edges = [
    ['A', 'B', ['weight' => 1.0]],
    ['B', 'C', ['weight' => 2.0]],
    ['C', 'D', ['weight' => 1.5]],
    ['D', 'A', ['weight' => 3.0]],
];

$graph = Graph::fromEdgeList($edges, directed: true);
```

## 🔍 Advanced Features

### Subgraph Views

Create efficient, read-only views of a subset of nodes:

```php
use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\Domain\SubgraphView;

// Create a graph
$graph = new Graph();
$graph->addEdge('A', 'B');
$graph->addEdge('B', 'C');
$graph->addEdge('C', 'D');
$graph->addEdge('D', 'E');

// Create a view containing only nodes A, B, and C
$subgraph = new SubgraphView($graph, ['A', 'B', 'C']);

// The view only shows edges within the selected nodes
$subgraph->nodes();  // ['A', 'B', 'C']
$subgraph->edges();  // Only A->B and B->C edges
$subgraph->hasEdge('C', 'D'); // false (D not in view)
```

### Modifying Attributes

```php
// Update node attributes (merge with existing)
$graph->addNode('A', ['new_attr' => 'value']);

// Replace all node attributes
$graph->setNodeAttrs('A', ['only' => 'this']);

// Update edge attributes
$graph->setEdgeAttrs('A', 'B', ['weight' => 5.0, 'label' => 'Strong']);
```

## 📤 Export Formats

### Cytoscape.js JSON

Export graphs for visualization with [Cytoscape.js](https://js.cytoscape.org/):

```php
use Mbsoft\Graph\IO\CytoscapeJsonExporter;

$exporter = new CytoscapeJsonExporter();
$json = $exporter->export($graph);

// Result structure:
// [
//     'elements' => [
//         'nodes' => [
//             ['data' => ['id' => 'A', 'label' => 'Node A', ...]],
//             ...
//         ],
//         'edges' => [
//             ['data' => ['source' => 'A', 'target' => 'B', 'weight' => 1.5]],
//             ...
//         ]
//     ]
// ]

file_put_contents('graph.json', json_encode($json));
```

### GraphML (XML)

Export to GraphML format for use with tools like Gephi, yEd, or NetworkX:

```php
use Mbsoft\Graph\IO\GraphMLExporter;

$exporter = new GraphMLExporter();
$xml = $exporter->export($graph);

file_put_contents('graph.graphml', $xml);
```

### GEXF (XML)

Export to GEXF format for Gephi and other network analysis tools:

```php
use Mbsoft\Graph\IO\GexfExporter;

$exporter = new GexfExporter();
$xml = $exporter->export($graph);

file_put_contents('graph.gexf', $xml);
```

## 🏗️ Architecture

### Interfaces

- **`GraphInterface`**: Read-only graph operations
- **`MutableGraphInterface`**: Extends GraphInterface with modification methods
- **`ExporterInterface`**: Common interface for all export formats

### Core Classes

- **`Graph`**: The main mutable graph implementation
- **`SubgraphView`**: Efficient filtered view of a graph
- **`Node`**: Immutable value object for nodes
- **`Edge`**: Immutable value object for edges
- **`IndexMap`**: Internal bidirectional mapping for performance

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

Run static analysis:

```bash
composer analyse
```

## 🎯 Use Cases

This library is perfect for:

- **Network Analysis**: Social networks, communication networks, infrastructure
- **Dependency Graphs**: Package dependencies, task scheduling, build systems
- **Pathfinding**: Route planning, game AI, logistics optimization
- **Data Visualization**: Creating interactive graph visualizations
- **Knowledge Graphs**: Semantic networks, ontologies, concept maps
- **Workflow Management**: Process flows, state machines, decision trees

## ⚡ Performance Considerations

The library is optimized for performance:

- **Integer Indexing**: Internally uses integer indices for O(1) lookups
- **Lazy Evaluation**: Subgraph views don't copy data
- **Memory Efficient**: Adjacency lists only store actual connections
- **Cache Friendly**: Data structures optimized for CPU cache locality

### Benchmarks

Performance with a 1,000 node graph:
- Node lookup: < 0.001ms
- Edge check: < 0.001ms
- Get successors: < 0.01ms
- Add edge: < 0.01ms

## 📚 Example Applications

### Social Network Analysis

```php
$socialNetwork = new Graph(directed: false);

// Add users
$socialNetwork->addNode('alice', ['name' => 'Alice', 'age' => 28]);
$socialNetwork->addNode('bob', ['name' => 'Bob', 'age' => 32]);
$socialNetwork->addNode('charlie', ['name' => 'Charlie', 'age' => 25]);

// Add friendships
$socialNetwork->addEdge('alice', 'bob', ['since' => '2020']);
$socialNetwork->addEdge('bob', 'charlie', ['since' => '2019']);

// Find friends of friends
$bobsFriends = $socialNetwork->successors('bob'); // ['alice', 'charlie']
```

### Task Dependency Graph

```php
$tasks = new Graph(directed: true);

// Add tasks
$tasks->addNode('compile', ['duration' => 30]);
$tasks->addNode('test', ['duration' => 45]);
$tasks->addNode('package', ['duration' => 15]);
$tasks->addNode('deploy', ['duration' => 20]);

// Add dependencies
$tasks->addEdge('compile', 'test');
$tasks->addEdge('test', 'package');
$tasks->addEdge('package', 'deploy');

// Find what needs to be done before deployment
$deployPrereqs = $tasks->predecessors('deploy'); // ['package']
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Inspired by NetworkX (Python) and JGraphT (Java)
- Built with modern PHP best practices
- Tested with Pest PHP testing framework

## 📮 Support

For bugs and feature requests, please use the [GitHub issues page](https://github.com/nexus-scholar/graph-core/issues).

## 🔗 See Also

- [mbsoft/graph-builder](https://github.com/mbsoft/graph-builder) - Fluent builders and factories (Phase 2)
- [mbsoft/graph-algorithms](https://github.com/mbsoft/graph-algorithms) - Graph algorithms (Phase 3)
- [mbsoft/graph-viz](https://github.com/mbsoft/graph-viz) - Visualization components (Phase 4)
