<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit63d7b2229e3c3fbb1637a43128f36d51
{
    public static $files = array (
        '78f278b8d5e25c06d1f4ed3594fbf783' => __DIR__ . '/../..' . '/src/Schema.php',
        'f4357a3582cc07033c65f86a5aa6ea9f' => __DIR__ . '/../..' . '/src/GF_Export_Screen.php',
    );

    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GravityKit\\GravityImport\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GravityKit\\GravityImport\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'G' => 
        array (
            'Goodby\\CSV' => 
            array (
                0 => __DIR__ . '/..' . '/goodby/csv/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Goodby\\CSV\\Export\\Protocol\\Exception\\IOException' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Protocol/Exception/IOException.php',
        'Goodby\\CSV\\Export\\Protocol\\ExporterInterface' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Protocol/ExporterInterface.php',
        'Goodby\\CSV\\Export\\Standard\\Collection\\CallbackCollection' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/Collection/CallbackCollection.php',
        'Goodby\\CSV\\Export\\Standard\\Collection\\PdoCollection' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/Collection/PdoCollection.php',
        'Goodby\\CSV\\Export\\Standard\\CsvFileObject' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/CsvFileObject.php',
        'Goodby\\CSV\\Export\\Standard\\Exception\\StrictViolationException' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/Exception/StrictViolationException.php',
        'Goodby\\CSV\\Export\\Standard\\Exporter' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/Exporter.php',
        'Goodby\\CSV\\Export\\Standard\\ExporterConfig' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Standard/ExporterConfig.php',
        'Goodby\\CSV\\Export\\Tests\\Protocol\\ExporterInterfaceTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Protocol/ExporterInterfaceTest.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Join\\Collection\\PdoCollectionTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Join/Collection/PdoCollectionTest.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Join\\ExporterTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Join/ExporterTest.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Join\\UsageTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Join/UsageTest.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Unit\\Collection\\CallbackCollectionTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Unit/Collection/CallbackCollectionTest.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Unit\\Collection\\SampleAggIterator' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Unit/Collection/SampleAggIterator.php',
        'Goodby\\CSV\\Export\\Tests\\Standard\\Unit\\ExporterConfigTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Export/Tests/Standard/Unit/ExporterConfigTest.php',
        'Goodby\\CSV\\Import\\Protocol\\Exception\\CsvFileNotFoundException' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Protocol/Exception/CsvFileNotFoundException.php',
        'Goodby\\CSV\\Import\\Protocol\\Exception\\InvalidLexicalException' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Protocol/Exception/InvalidLexicalException.php',
        'Goodby\\CSV\\Import\\Protocol\\InterpreterInterface' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Protocol/InterpreterInterface.php',
        'Goodby\\CSV\\Import\\Protocol\\LexerInterface' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Protocol/LexerInterface.php',
        'Goodby\\CSV\\Import\\Standard\\Exception\\StrictViolationException' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/Exception/StrictViolationException.php',
        'Goodby\\CSV\\Import\\Standard\\Interpreter' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/Interpreter.php',
        'Goodby\\CSV\\Import\\Standard\\Lexer' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/Lexer.php',
        'Goodby\\CSV\\Import\\Standard\\LexerConfig' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/LexerConfig.php',
        'Goodby\\CSV\\Import\\Standard\\Observer\\PdoObserver' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/Observer/PdoObserver.php',
        'Goodby\\CSV\\Import\\Standard\\Observer\\SqlObserver' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/Observer/SqlObserver.php',
        'Goodby\\CSV\\Import\\Standard\\StreamFilter\\ConvertMbstringEncoding' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Standard/StreamFilter/ConvertMbstringEncoding.php',
        'Goodby\\CSV\\Import\\Tests\\Protocol\\InterpreterTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Protocol/InterpreterTest.php',
        'Goodby\\CSV\\Import\\Tests\\Protocol\\LexerTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Protocol/LexerTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Join\\CSVFiles' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Join/CSVFiles.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Join\\LexerTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Join/LexerTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Join\\Observer\\PdoObserverTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Join/Observer/PdoObserverTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\SandboxDirectoryManager' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/SandboxDirectoryManager.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Unit\\InterpreterTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Unit/InterpreterTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Unit\\LexerConfigTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Unit/LexerConfigTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Unit\\Observer\\SqlObserverTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Unit/Observer/SqlObserverTest.php',
        'Goodby\\CSV\\Import\\Tests\\Standard\\Unit\\StreamFilter\\ConvertMbstringEncodingTest' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/Import/Tests/Standard/Unit/StreamFilter/ConvertMbstringEncodingTest.php',
        'Goodby\\CSV\\TestHelper\\DbManager' => __DIR__ . '/..' . '/goodby/csv/src/Goodby/CSV/TestHelper/DbManager.php',
        'GravityKit\\GravityImport\\Addon' => __DIR__ . '/../..' . '/src/Addon.php',
        'GravityKit\\GravityImport\\Batch' => __DIR__ . '/../..' . '/src/Batch.php',
        'GravityKit\\GravityImport\\Compat' => __DIR__ . '/../..' . '/src/Compat.php',
        'GravityKit\\GravityImport\\Core' => __DIR__ . '/../..' . '/src/Core.php',
        'GravityKit\\GravityImport\\GF_Entries_Screen' => __DIR__ . '/../..' . '/src/GF_Entries_Screen.php',
        'GravityKit\\GravityImport\\GF_System_Status_Screen' => __DIR__ . '/../..' . '/src/GF_System_Status_Screen.php',
        'GravityKit\\GravityImport\\Log' => __DIR__ . '/../..' . '/src/Log.php',
        'GravityKit\\GravityImport\\Processor' => __DIR__ . '/../..' . '/src/Processor.php',
        'GravityKit\\GravityImport\\REST_Batch_Controller' => __DIR__ . '/../..' . '/src/REST_Batch_Controller.php',
        'GravityKit\\GravityImport\\UI' => __DIR__ . '/../..' . '/src/UI.php',
        'GravityKit\\GravityImport\\WP_Import_Screen' => __DIR__ . '/../..' . '/src/WP_Import_Screen.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit63d7b2229e3c3fbb1637a43128f36d51::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit63d7b2229e3c3fbb1637a43128f36d51::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit63d7b2229e3c3fbb1637a43128f36d51::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit63d7b2229e3c3fbb1637a43128f36d51::$classMap;

        }, null, ClassLoader::class);
    }
}
