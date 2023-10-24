<?php
namespace ccxt;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');
ini_set('memory_limit', '512M');

define('rootDir', __DIR__ . '/../../');
include_once rootDir .'/vendor/autoload.php';
use React\Async;
use React\Promise;

assert_options (ASSERT_CALLBACK, function(string $file, int $line, ?string $assertion, string $description = null){
    $args = func_get_args();
    $message = '';
    try {
        $message = "[ASSERT_ERROR] - [ $file : $line ] $description";
    } catch (\Exception $exc) {
        $message = "[ASSERT_ERROR] -" . json_encode($args);
    }
    $message = substr($message, 0, LOG_CHARS_LENGTH);
    dump($message);
    exit;
});

$filetered_args = array_filter(array_map (function ($x) { return stripos($x,'--')===false? $x : null;} , $argv));
$exchangeId = array_key_exists(1, $filetered_args) ? $filetered_args[1] : null; // this should be different than JS
$exchangeSymbol = null; // todo: this should be different than JS

// non-transpiled part, but shared names among langs

class baseMainTestClass {
    public $testFiles = [];
    public $skippedMethods = [];
    public $checkedPublicTests = [];
    public $publicTests = [];
    public $info = false;
    public $verbose = false;
    public $debug = false;
    public $privateTest = false;
    public $privateTestOnly = false;
    public $sandbox = false;
}

define ('is_synchronous', stripos(__FILE__, '_async') === false);

define('rootDirForSkips', __DIR__ . '/../../');
define('envVars', $_ENV);
define('LOG_CHARS_LENGTH', 10000);
define('ext', 'php');

function dump(...$s) {
    $args = array_map(function ($arg) {
        if (is_array($arg) || is_object($arg)) {
            return json_encode($arg);
        } else {
            return $arg;
        }
    }, func_get_args());
    echo implode(' ', $args) . "\n";
}

function get_cli_arg_value ($arg) {
    return in_array($arg, $GLOBALS['argv']);
}

function get_test_name($methodName) {
    $snake_cased = strtolower(preg_replace('/(?<!^)(?=[A-Z])/', '_', $methodName)); // snake_case
    $snake_cased = str_replace('o_h_l_c_v', 'ohlcv', $snake_cased);
    return 'test_' . $snake_cased;
}

function io_file_exists($path) {
    return file_exists($path);
}

function io_file_read($path, $decode = true) {
    $content = file_get_contents($path);
    return $decode ? json_decode($content, true) : $content;
}

function call_method($testFiles, $methodName, $exchange, $skippedProperties, $args) {
    return $testFiles[$methodName]($exchange, $skippedProperties, ... $args);
}

function exception_message($exc) {
    $full_trace = $exc->getTrace();
    // temporarily disable below line, so we dump whole array
    // $items = array_slice($full_trace, 0, 12); // 12 members are enough for proper trace 
    $items = $full_trace;
    $output = '';
    foreach ($items as $item) {
        if (array_key_exists('file', $item)) {
            $output .= $item['file'];
            if (array_key_exists('line', $item)) {
                $output .= ':' . $item['line'];
            }
            if (array_key_exists('class', $item)) {
                $output .= ' ::: ' . $item['class'];
            }
            if (array_key_exists('function', $item)) {
                $output .= ' > ' . $item['function'];
            }
            $output .= "\n";
        }
    }
    $message = '[' . get_class($exc) . '] ' . $output . "\n\n";
    return substr($message, 0, LOG_CHARS_LENGTH);
}

function compare_exception_type($exc, $exceptionType) {
    // $classFullName = '\\' . get_class($exc);
    // return $classFullName === $exceptionType; (`$exceptionType` is string here)
    //
    // update: for now we check the exception and it's inheritances
    return ($exc instanceof $exceptionType);
}

function exit_script() {
    exit(0);
}

function get_exchange_prop ($exchange, $prop, $defaultValue = null) {
    return property_exists ($exchange, $prop) ? $exchange->{$prop} : $defaultValue;
}

function set_exchange_prop ($exchange, $prop, $value) {
    $exchange->{$prop} = $value;
}

function init_exchange ($exchangeId, $args) {
    $exchangeClassString = '\\ccxt\\' . (is_synchronous ? '' : 'async\\') . $exchangeId;
    return new $exchangeClassString($args);
}

function set_test_files ($holderClass, $properties) {
    return Async\async (function() use ($holderClass, $properties){
        $skiped = ['test_throttle'];
        foreach (glob(__DIR__ . '/' . (is_synchronous ? 'sync' : 'async') . '/test_*.php') as $filename) {
            $basename = basename($filename);
            if (!in_array($basename, $skiped)) {
                include_once $filename;
            }
        }
        $allfuncs = get_defined_functions()['user'];
        foreach ($allfuncs as $fName) {
            if (stripos($fName, 'ccxt\\test_')!==false) {
                $nameWithoutNs = str_replace('ccxt\\', '', $fName);
                $holderClass->testFiles[$nameWithoutNs] = $fName;
            }
        }
    })();
}

function close($exchange) {
    return Async\async (function() {
        // stub
        return true;
    })();
}

// *********************************
// ***** AUTO-TRANSPILER-START *****


// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception; // a common import

use ccxt\NotSupported;
use ccxt\NetworkError;
use ccxt\ExchangeNotAvailable;
use ccxt\InvalidNonce;
use ccxt\OnMaintenance;
use ccxt\AuthenticationError;

class testMainClass extends baseMainTestClass {

    public function parse_cli_args() {
        $this->info = get_cli_arg_value ('--info');
        $this->verbose = get_cli_arg_value ('--verbose');
        $this->debug = get_cli_arg_value ('--debug');
        $this->privateTest = get_cli_arg_value ('--private');
        $this->privateTestOnly = get_cli_arg_value ('--privateOnly');
        $this->sandbox = get_cli_arg_value ('--sandbox');
    }

    public function init($exchangeId, $symbol) {
        return Async\async(function () use ($exchangeId, $symbol) {
            $this->parse_cli_args();
            $symbolStr = $symbol !== null ? $symbol : 'all';
            dump ('\nTESTING ', ext, array( 'exchange' => $exchangeId, 'symbol' => $symbolStr ), '\n');
            $exchangeArgs = array(
                'verbose' => $this->verbose,
                'debug' => $this->debug,
                'enableRateLimit' => true,
                'timeout' => 30000,
            );
            $exchange = init_exchange ($exchangeId, $exchangeArgs);
            Async\await($this->import_files($exchange));
            $this->expand_settings($exchange, $symbol);
            Async\await($this->start_test($exchange, $symbol));
        }) ();
    }

    public function import_files($exchange) {
        return Async\async(function () use ($exchange) {
            // $exchange tests
            $this->testFiles = array();
            $properties = is_array($exchange->has) ? array_keys($exchange->has) : array();
            $properties[] = 'loadMarkets';
            Async\await(set_test_files ($this, $properties));
        }) ();
    }

    public function expand_settings($exchange, $symbol) {
        $exchangeId = $exchange->id;
        $keysGlobal = rootDir . 'keys.json';
        $keysLocal = rootDir . 'keys.local.json';
        $keysGlobalExists = io_file_exists ($keysGlobal);
        $keysLocalExists = io_file_exists ($keysLocal);
        $globalSettings = $keysGlobalExists ? io_file_read ($keysGlobal) : array();
        $localSettings = $keysLocalExists ? io_file_read ($keysLocal) : array();
        $allSettings = $exchange->deep_extend($globalSettings, $localSettings);
        $exchangeSettings = $exchange->safe_value($allSettings, $exchangeId, array());
        if ($exchangeSettings) {
            $settingKeys = is_array($exchangeSettings) ? array_keys($exchangeSettings) : array();
            for ($i = 0; $i < count($settingKeys); $i++) {
                $key = $settingKeys[$i];
                if ($exchangeSettings[$key]) {
                    $finalValue = null;
                    if (gettype($exchangeSettings[$key]) === 'array') {
                        $existing = get_exchange_prop ($exchange, $key, array());
                        $finalValue = $exchange->deep_extend($existing, $exchangeSettings[$key]);
                    } else {
                        $finalValue = $exchangeSettings[$key];
                    }
                    set_exchange_prop ($exchange, $key, $finalValue);
                }
            }
        }
        // credentials
        $reqCreds = get_exchange_prop ($exchange, 're' . 'quiredCredentials'); // dont glue the r-e-q-u-$i-r-e phrase, because leads to messed up transpilation
        $objkeys = is_array($reqCreds) ? array_keys($reqCreds) : array();
        for ($i = 0; $i < count($objkeys); $i++) {
            $credential = $objkeys[$i];
            $isRequired = $reqCreds[$credential];
            if ($isRequired && get_exchange_prop ($exchange, $credential) === null) {
                $fullKey = $exchangeId . '_' . $credential;
                $credentialEnvName = strtoupper($fullKey); // example => KRAKEN_APIKEY
                $credentialValue = (is_array(envVars) && array_key_exists($credentialEnvName, envVars)) ? envVars[$credentialEnvName] : null;
                if ($credentialValue) {
                    set_exchange_prop ($exchange, $credential, $credentialValue);
                }
            }
        }
        // skipped tests
        $skippedFile = rootDirForSkips . 'skip-tests.json';
        $skippedSettings = io_file_read ($skippedFile);
        $skippedSettingsForExchange = $exchange->safe_value($skippedSettings, $exchangeId, array());
        // others
        $timeout = $exchange->safe_value($skippedSettingsForExchange, 'timeout');
        if ($timeout !== null) {
            $exchange->timeout = $timeout;
        }
        $exchange->httpsProxy = $exchange->safe_string($skippedSettingsForExchange, 'httpsProxy');
        $this->skippedMethods = $exchange->safe_value($skippedSettingsForExchange, 'skipMethods', array());
        $this->checkedPublicTests = array();
    }

    public function add_padding($message, $size) {
        // has to be transpilable
        $res = '';
        $missingSpace = $size - strlen($message) - 0; // - 0 is added just to trick transpile to treat the .length string for php
        if ($missingSpace > 0) {
            for ($i = 0; $i < $missingSpace; $i++) {
                $res .= ' ';
            }
        }
        return $message . $res;
    }

    public function test_method($methodName, $exchange, $args, $isPublic) {
        return Async\async(function () use ($methodName, $exchange, $args, $isPublic) {
            $isLoadMarkets = ($methodName === 'loadMarkets');
            $methodNameInTest = get_test_name ($methodName);
            // if this is a private test, and the implementation was already tested in public, then no need to re-test it in private test (exception is fetchCurrencies, because our approach in base $exchange)
            if (!$isPublic && (is_array($this->checkedPublicTests) && array_key_exists($methodNameInTest, $this->checkedPublicTests)) && ($methodName !== 'fetchCurrencies')) {
                return;
            }
            $skipMessage = null;
            if (!$isLoadMarkets && (!(is_array($exchange->has) && array_key_exists($methodName, $exchange->has)) || !$exchange->has[$methodName])) {
                $skipMessage = '[INFO:UNSUPPORTED_TEST]'; // keep it aligned with the longest message
            } elseif ((is_array($this->skippedMethods) && array_key_exists($methodName, $this->skippedMethods)) && (gettype($this->skippedMethods[$methodName]) === 'string')) {
                $skipMessage = '[INFO:SKIPPED_TEST]';
            } elseif (!(is_array($this->testFiles) && array_key_exists($methodNameInTest, $this->testFiles))) {
                $skipMessage = '[INFO:UNIMPLEMENTED_TEST]';
            }
            // exceptionally for `loadMarkets` call, we call it before it's even checked for "skip" need it to be called anyway (but can skip "test.loadMarket" for it)
            if ($isLoadMarkets) {
                Async\await($exchange->load_markets(true));
            }
            if ($skipMessage) {
                if ($this->info) {
                    dump ($this->add_padding($skipMessage, 25), $exchange->id, $methodNameInTest);
                }
                return;
            }
            if ($this->info) {
                $argsStringified = '(' . implode(',', $args) . ')';
                dump ($this->add_padding('[INFO:TESTING]', 25), $exchange->id, $methodNameInTest, $argsStringified);
            }
            $skippedProperties = $exchange->safe_value($this->skippedMethods, $methodName, array());
            Async\await(call_method ($this->testFiles, $methodNameInTest, $exchange, $skippedProperties, $args));
            // if it was passed successfully, add to the list of successfull tests
            if ($isPublic) {
                $this->checkedPublicTests[$methodNameInTest] = true;
            }
        }) ();
    }

    public function test_safe($methodName, $exchange, $args = [], $isPublic = false) {
        return Async\async(function () use ($methodName, $exchange, $args, $isPublic) {
            // `testSafe` method does not throw an exception, instead mutes it.
            // The reason we mute the thrown exceptions here is because if this test is part
            // of "runPublicTests", then we don't want to stop the whole test if any single
            // test-method fails. For example, if "fetchOrderBook" public test fails, we still
            // want to run "fetchTickers" and other methods. However, independently this fact,
            // from those test-methods we still echo-out (var_dump/print...) the exception
            // messages with specific formatted message "[TEST_FAILURE] ..." and that output is
            // then regex-parsed by run-tests.js, so the exceptions are still printed out to
            // console from there. So, even if some public tests fail, the script will continue
            // doing other things (testing other spot/swap or private tests ...)
            $maxRetries = 3;
            $argsStringified = $exchange->json ($args); // $args->join() breaks when we provide a list of symbols | "args.toString()" breaks bcz of "array to string conversion"
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    Async\await($this->test_method($methodName, $exchange, $args, $isPublic));
                    return true;
                } catch (Exception $e) {
                    $isAuthError = ($e instanceof AuthenticationError);
                    $isRateLimitExceeded = ($e instanceof RateLimitExceeded);
                    $isNetworkError = ($e instanceof NetworkError);
                    $isDDoSProtection = ($e instanceof DDoSProtection);
                    $isRequestTimeout = ($e instanceof RequestTimeout);
                    $isNotSupported = ($e instanceof NotSupported);
                    $tempFailure = ($isRateLimitExceeded || $isNetworkError || $isDDoSProtection || $isRequestTimeout);
                    if ($tempFailure) {
                        // if last retry was gone with same `$tempFailure` error, then let's eventually return false
                        if ($i === $maxRetries - 1) {
                            dump ('[TEST_WARNING]', 'Method could not be tested due to a repeated Network/Availability issues', ' | ', $exchange->id, $methodName, $argsStringified);
                        } else {
                            // wait and retry again
                            Async\await($exchange->sleep ($i * 1000)); // increase wait seconds on every retry
                            continue;
                        }
                    } elseif ($e instanceof OnMaintenance) {
                        // in case of maintenance, skip $exchange (don't fail the test)
                        dump ('[TEST_WARNING] Exchange is on maintenance', $exchange->id);
                    }
                    // If public test faces authentication error, we don't break (see comments under `testSafe` method)
                    else if ($isPublic && $isAuthError) {
                        // in case of loadMarkets, it means that "tester" (developer or travis) does not have correct authentication, so it does not have a point to proceed at all
                        if ($methodName === 'loadMarkets') {
                            dump ('[TEST_WARNING]', 'Exchange can not be tested, because of authentication problems during loadMarkets', exception_message ($e), $exchange->id, $methodName, $argsStringified);
                        }
                        if ($this->info) {
                            dump ('[TEST_WARNING]', 'Authentication problem for public method', exception_message ($e), $exchange->id, $methodName, $argsStringified);
                        }
                    } else {
                        // if not a temporary connectivity issue, then mark test (no need to re-try)
                        if ($isNotSupported) {
                            dump ('[NOT_SUPPORTED]', $exchange->id, $methodName, $argsStringified);
                            return true; // why consider not supported failed test?
                        } else {
                            dump ('[TEST_FAILURE]', exception_message ($e), $exchange->id, $methodName, $argsStringified);
                        }
                    }
                    return false;
                }
            }
        }) ();
    }

    public function run_public_tests($exchange, $symbol) {
        return Async\async(function () use ($exchange, $symbol) {
            $tests = array(
                'fetchCurrencies' => array(),
                'fetchTicker' => array( $symbol ),
                'fetchTickers' => array( $symbol ),
                'fetchOHLCV' => array( $symbol ),
                'fetchTrades' => array( $symbol ),
                'fetchOrderBook' => array( $symbol ),
                'fetchL2OrderBook' => array( $symbol ),
                'fetchOrderBooks' => array(),
                'fetchBidsAsks' => array(),
                'fetchStatus' => array(),
                'fetchTime' => array(),
            );
            $market = $exchange->market ($symbol);
            $isSpot = $market['spot'];
            if ($isSpot) {
                $tests['fetchCurrencies'] = array();
            } else {
                $tests['fetchFundingRates'] = array( $symbol );
                $tests['fetchFundingRate'] = array( $symbol );
                $tests['fetchFundingRateHistory'] = array( $symbol );
                $tests['fetchIndexOHLCV'] = array( $symbol );
                $tests['fetchMarkOHLCV'] = array( $symbol );
                $tests['fetchPremiumIndexOHLCV'] = array( $symbol );
            }
            $this->publicTests = $tests;
            $testNames = is_array($tests) ? array_keys($tests) : array();
            $promises = array();
            for ($i = 0; $i < count($testNames); $i++) {
                $testName = $testNames[$i];
                $testArgs = $tests[$testName];
                $promises[] = $this->test_safe($testName, $exchange, $testArgs, true);
            }
            // todo - not yet ready in other langs too
            // $promises[] = testThrottle ();
            $results = Async\await(Promise\all($promises));
            // now count which test-methods retuned `false` from "testSafe" and dump that info below
            if ($this->info) {
                $errors = array();
                for ($i = 0; $i < count($testNames); $i++) {
                    if (!$results[$i]) {
                        $errors[] = $testNames[$i];
                    }
                }
                // we don't throw exception for public-$tests, see comments under 'testSafe' method
                $failedMsg = '';
                $errorsLength = count($errors);
                if ($errorsLength > 0) {
                    $failedMsg = ' | Failed methods : ' . implode(', ', $errors);
                }
                dump ($this->add_padding('[INFO:PUBLIC_TESTS_END] ' . $market['type'] . $failedMsg, 25), $exchange->id);
            }
        }) ();
    }

    public function load_exchange($exchange) {
        return Async\async(function () use ($exchange) {
            $result = Async\await($this->test_safe('loadMarkets', $exchange, array(), true));
            if (!$result) {
                return false;
            }
            $symbols = array(
                'BTC/CNY',
                'BTC/USD',
                'BTC/USDT',
                'BTC/EUR',
                'BTC/ETH',
                'ETH/BTC',
                'BTC/JPY',
                'ETH/EUR',
                'ETH/JPY',
                'ETH/CNY',
                'ETH/USD',
                'LTC/CNY',
                'DASH/BTC',
                'DOGE/BTC',
                'BTC/AUD',
                'BTC/PLN',
                'USD/SLL',
                'BTC/RUB',
                'BTC/UAH',
                'LTC/BTC',
                'EUR/USD',
            );
            $resultSymbols = array();
            $exchangeSpecificSymbols = $exchange->symbols;
            for ($i = 0; $i < count($exchangeSpecificSymbols); $i++) {
                $symbol = $exchangeSpecificSymbols[$i];
                if ($exchange->in_array($symbol, $symbols)) {
                    $resultSymbols[] = $symbol;
                }
            }
            $resultMsg = '';
            $resultLength = count($resultSymbols);
            $exchangeSymbolsLength = count($exchange->symbols);
            if ($resultLength > 0) {
                if ($exchangeSymbolsLength > $resultLength) {
                    $resultMsg = implode(', ', $resultSymbols) . ' . more...';
                } else {
                    $resultMsg = implode(', ', $resultSymbols);
                }
            }
            dump ('Exchange loaded', $exchangeSymbolsLength, 'symbols', $resultMsg);
            return true;
        }) ();
    }

    public function get_test_symbol($exchange, $isSpot, $symbols) {
        $symbol = null;
        for ($i = 0; $i < count($symbols); $i++) {
            $s = $symbols[$i];
            $market = $exchange->safe_value($exchange->markets, $s);
            if ($market !== null) {
                $active = $exchange->safe_value($market, 'active');
                if ($active || ($active === null)) {
                    $symbol = $s;
                    break;
                }
            }
        }
        return $symbol;
    }

    public function get_exchange_code($exchange, $codes = null) {
        if ($codes === null) {
            $codes = array( 'BTC', 'ETH', 'XRP', 'LTC', 'BCH', 'EOS', 'BNB', 'BSV', 'USDT' );
        }
        $code = $codes[0];
        for ($i = 0; $i < count($codes); $i++) {
            if (is_array($exchange->currencies) && array_key_exists($codes[$i], $exchange->currencies)) {
                return $codes[$i];
            }
        }
        return $code;
    }

    public function get_markets_from_exchange($exchange, $spot = true) {
        $res = array();
        $markets = $exchange->markets;
        $keys = is_array($markets) ? array_keys($markets) : array();
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $market = $markets[$key];
            if ($spot && $market['spot']) {
                $res[$market['symbol']] = $market;
            } elseif (!$spot && !$market['spot']) {
                $res[$market['symbol']] = $market;
            }
        }
        return $res;
    }

    public function get_valid_symbol($exchange, $spot = true) {
        $currentTypeMarkets = $this->get_markets_from_exchange($exchange, $spot);
        $codes = array(
            'BTC',
            'ETH',
            'XRP',
            'LTC',
            'BCH',
            'EOS',
            'BNB',
            'BSV',
            'USDT',
            'ATOM',
            'BAT',
            'BTG',
            'DASH',
            'DOGE',
            'ETC',
            'IOTA',
            'LSK',
            'MKR',
            'NEO',
            'PAX',
            'QTUM',
            'TRX',
            'TUSD',
            'USD',
            'USDC',
            'WAVES',
            'XEM',
            'XMR',
            'ZEC',
            'ZRX',
        );
        $spotSymbols = array(
            'BTC/USD',
            'BTC/USDT',
            'BTC/CNY',
            'BTC/EUR',
            'BTC/ETH',
            'ETH/BTC',
            'ETH/USD',
            'ETH/USDT',
            'BTC/JPY',
            'LTC/BTC',
            'ZRX/WETH',
            'EUR/USD',
        );
        $swapSymbols = array(
            'BTC/USDT:USDT',
            'BTC/USD:USD',
            'ETH/USDT:USDT',
            'ETH/USD:USD',
            'LTC/USDT:USDT',
            'DOGE/USDT:USDT',
            'ADA/USDT:USDT',
            'BTC/USD:BTC',
            'ETH/USD:ETH',
        );
        $targetSymbols = $spot ? $spotSymbols : $swapSymbols;
        $symbol = $this->get_test_symbol($exchange, $spot, $targetSymbols);
        // if symbols wasn't found from above hardcoded list, then try to locate any $symbol which has our target hardcoded 'base' code
        if ($symbol === null) {
            for ($i = 0; $i < count($codes); $i++) {
                $currentCode = $codes[$i];
                $marketsArrayForCurrentCode = $exchange->filter_by($currentTypeMarkets, 'base', $currentCode);
                $indexedMkts = $exchange->index_by($marketsArrayForCurrentCode, 'symbol');
                $symbolsArrayForCurrentCode = is_array($indexedMkts) ? array_keys($indexedMkts) : array();
                $symbolsLength = count($symbolsArrayForCurrentCode);
                if ($symbolsLength) {
                    $symbol = $this->get_test_symbol($exchange, $spot, $symbolsArrayForCurrentCode);
                    break;
                }
            }
        }
        // if there wasn't found any $symbol with our hardcoded 'base' code, then just try to find symbols that are 'active'
        if ($symbol === null) {
            $activeMarkets = $exchange->filter_by($currentTypeMarkets, 'active', true);
            $activeSymbols = array();
            for ($i = 0; $i < count($activeMarkets); $i++) {
                $activeSymbols[] = $activeMarkets[$i]['symbol'];
            }
            $symbol = $this->get_test_symbol($exchange, $spot, $activeSymbols);
        }
        if ($symbol === null) {
            $values = is_array($currentTypeMarkets) ? array_values($currentTypeMarkets) : array();
            $valuesLength = count($values);
            if ($valuesLength > 0) {
                $first = $values[0];
                if ($first !== null) {
                    $symbol = $first['symbol'];
                }
            }
        }
        return $symbol;
    }

    public function test_exchange($exchange, $providedSymbol = null) {
        return Async\async(function () use ($exchange, $providedSymbol) {
            $spotSymbol = null;
            $swapSymbol = null;
            if ($providedSymbol !== null) {
                $market = $exchange->market ($providedSymbol);
                if ($market['spot']) {
                    $spotSymbol = $providedSymbol;
                } else {
                    $swapSymbol = $providedSymbol;
                }
            } else {
                if ($exchange->has['spot']) {
                    $spotSymbol = $this->get_valid_symbol($exchange, true);
                }
                if ($exchange->has['swap']) {
                    $swapSymbol = $this->get_valid_symbol($exchange, false);
                }
            }
            if ($spotSymbol !== null) {
                dump ('Selected SPOT SYMBOL:', $spotSymbol);
            }
            if ($swapSymbol !== null) {
                dump ('Selected SWAP SYMBOL:', $swapSymbol);
            }
            if (!$this->privateTestOnly) {
                if ($exchange->has['spot'] && $spotSymbol !== null) {
                    if ($this->info) {
                        dump ('[INFO:SPOT TESTS]');
                    }
                    $exchange->options['type'] = 'spot';
                    Async\await($this->run_public_tests($exchange, $spotSymbol));
                }
                if ($exchange->has['swap'] && $swapSymbol !== null) {
                    if ($this->info) {
                        dump ('[INFO:SWAP TESTS]');
                    }
                    $exchange->options['type'] = 'swap';
                    Async\await($this->run_public_tests($exchange, $swapSymbol));
                }
            }
            if ($this->privateTest || $this->privateTestOnly) {
                if ($exchange->has['spot'] && $spotSymbol !== null) {
                    $exchange->options['defaultType'] = 'spot';
                    Async\await($this->run_private_tests($exchange, $spotSymbol));
                }
                if ($exchange->has['swap'] && $swapSymbol !== null) {
                    $exchange->options['defaultType'] = 'swap';
                    Async\await($this->run_private_tests($exchange, $swapSymbol));
                }
            }
        }) ();
    }

    public function run_private_tests($exchange, $symbol) {
        return Async\async(function () use ($exchange, $symbol) {
            if (!$exchange->check_required_credentials(false)) {
                dump ('[Skipping private $tests]', 'Keys not found');
                return;
            }
            $code = $this->get_exchange_code($exchange);
            // if ($exchange->extendedTest) {
            //     Async\await(test ('InvalidNonce', $exchange, $symbol));
            //     Async\await(test ('OrderNotFound', $exchange, $symbol));
            //     Async\await(test ('InvalidOrder', $exchange, $symbol));
            //     Async\await(test ('InsufficientFunds', $exchange, $symbol, balance)); // danger zone - won't execute with non-empty balance
            // }
            $tests = array(
                'signIn' => [ ],
                'fetchBalance' => [ ],
                'fetchAccounts' => [ ],
                'fetchTransactionFees' => [ ],
                'fetchTradingFees' => [ ],
                'fetchStatus' => [ ],
                'fetchOrders' => array( $symbol ),
                'fetchOpenOrders' => array( $symbol ),
                'fetchClosedOrders' => array( $symbol ),
                'fetchMyTrades' => array( $symbol ),
                'fetchLeverageTiers' => array( array( $symbol ) ),
                'fetchLedger' => array( $code ),
                'fetchTransactions' => array( $code ),
                'fetchDeposits' => array( $code ),
                'fetchWithdrawals' => array( $code ),
                'fetchBorrowRates' => [ ],
                'fetchBorrowRate' => array( $code ),
                'fetchBorrowInterest' => array( $code, $symbol ),
                // 'addMargin' => [ ],
                // 'reduceMargin' => [ ],
                // 'setMargin' => [ ],
                // 'setMarginMode' => [ ],
                // 'setLeverage' => [ ],
                'cancelAllOrders' => array( $symbol ),
                // 'cancelOrder' => [ ],
                // 'cancelOrders' => [ ],
                'fetchCanceledOrders' => array( $symbol ),
                // 'fetchClosedOrder' => [ ],
                // 'fetchOpenOrder' => [ ],
                // 'fetchOrder' => [ ],
                // 'fetchOrderTrades' => [ ],
                'fetchPosition' => array( $symbol ),
                'fetchDeposit' => array( $code ),
                'createDepositAddress' => array( $code ),
                'fetchDepositAddress' => array( $code ),
                'fetchDepositAddresses' => array( $code ),
                'fetchDepositAddressesByNetwork' => array( $code ),
                // 'editOrder' => [ ],
                'fetchBorrowRateHistory' => array( $code ),
                'fetchBorrowRatesPerSymbol' => [ ],
                'fetchLedgerEntry' => array( $code ),
                // 'fetchWithdrawal' => [ ],
                // 'transfer' => [ ],
                // 'withdraw' => [ ],
            );
            $market = $exchange->market ($symbol);
            $isSpot = $market['spot'];
            if ($isSpot) {
                $tests['fetchCurrencies'] = [ ];
            } else {
                // derivatives only
                $tests['fetchPositions'] = array( $symbol ); // this test fetches all positions for 1 $symbol
                $tests['fetchPosition'] = array( $symbol );
                $tests['fetchPositionRisk'] = array( $symbol );
                $tests['setPositionMode'] = array( $symbol );
                $tests['setMarginMode'] = array( $symbol );
                $tests['fetchOpenInterestHistory'] = array( $symbol );
                $tests['fetchFundingRateHistory'] = array( $symbol );
                $tests['fetchFundingHistory'] = array( $symbol );
            }
            $combinedPublicPrivateTests = $exchange->deep_extend($this->publicTests, $tests);
            $testNames = is_array($combinedPublicPrivateTests) ? array_keys($combinedPublicPrivateTests) : array();
            $promises = array();
            for ($i = 0; $i < count($testNames); $i++) {
                $testName = $testNames[$i];
                $testArgs = $combinedPublicPrivateTests[$testName];
                $promises[] = $this->test_safe($testName, $exchange, $testArgs, false);
            }
            $results = Async\await(Promise\all($promises));
            $errors = array();
            for ($i = 0; $i < count($testNames); $i++) {
                $testName = $testNames[$i];
                $success = $results[$i];
                if (!$success) {
                    $errors[] = $testName;
                }
            }
            $errorsCnt = count($errors); // PHP transpile count($errors)
            if ($errorsCnt > 0) {
                // throw new \Exception('Failed private $tests [' . $market['type'] . '] => ' . implode(', ', $errors));
                dump ('[TEST_FAILURE]', 'Failed private $tests [' . $market['type'] . '] => ' . implode(', ', $errors));
            } else {
                if ($this->info) {
                    dump ($this->add_padding('[INFO:PRIVATE_TESTS_DONE]', 25), $exchange->id);
                }
            }
        }) ();
    }

    public function start_test($exchange, $symbol) {
        return Async\async(function () use ($exchange, $symbol) {
            // we do not need to test aliases
            if ($exchange->alias) {
                return;
            }
            if ($this->sandbox || get_exchange_prop ($exchange, 'sandbox')) {
                $exchange->set_sandbox_mode(true);
            }
            // because of python-async, we need proper `.close()` handling
            try {
                $result = Async\await($this->load_exchange($exchange));
                if (!$result) {
                    Async\await(close ($exchange));
                    return;
                }
                Async\await($this->test_exchange($exchange, $symbol));
                Async\await(close ($exchange));
            } catch (Exception $e) {
                Async\await(close ($exchange));
                throw $e;
            }
        }) ();
    }
}

// ***** AUTO-TRANSPILER-END *****
// *******************************
$promise = (new testMainClass())->init($exchangeId, $exchangeSymbol);
Async\await($promise);