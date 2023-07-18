// ----------------------------------------------------------------------------

import fs from 'fs';
import assert from 'assert';
import { Agent } from 'https';
import { fileURLToPath, pathToFileURL } from 'url';
import ccxt from '../../ccxt.js';
import errorsHierarchy from '../base/errorHierarchy.js';


// js specific codes //
const __dirname = fileURLToPath (new URL ('.', import.meta.url));
process.on ('uncaughtException', (e) => {
    console.log (e, e.stack); process.exit (1);
});
process.on ('unhandledRejection', (e: any) => {
    console.log (e, e.stack); process.exit (1);
});
const [ processPath, , exchangeId = null, exchangeSymbol = undefined ] = process.argv.filter ((x) => !x.startsWith ('--'));
const AuthenticationError = ccxt.AuthenticationError;
const RateLimitExceeded = ccxt.RateLimitExceeded;
const ExchangeNotAvailable = ccxt.ExchangeNotAvailable;
const NetworkError = ccxt.NetworkError;
const DDoSProtection = ccxt.DDoSProtection;
const OnMaintenance = ccxt.OnMaintenance;
const RequestTimeout = ccxt.RequestTimeout;

// non-transpiled part, but shared names among langs
class baseMainTestClass {
    info = false;
    verbose = false;
    debug = false;
    privateTest = false;
    privateTestOnly = false;
    sandbox = false;
    skippedMethods = {};
    checkedPublicTests = {};
    testFiles = {};
    publicTests = {};
}
const rootDir = __dirname + '/../../../';
const rootDirForSkips = __dirname + '/../../../';
const envVars = process.env;
const ext = import.meta.url.split ('.')[1];

function dump (...args) {
    console.log (...args);
}

function getCliArgValue (arg) {
    return process.argv.includes (arg) || false;
}

function getTestName (str) {
    return str;
}

function ioFileExists (path) {
    return fs.existsSync (path);
}

function ioFileRead (path, decode = true) {
    const content = fs.readFileSync (path, 'utf8');
    return decode ? JSON.parse (content) : content;
}

async function callMethod (testFiles, methodName, exchange, skippedProperties, args) {
    return await testFiles[methodName] (exchange, skippedProperties, ...args);
}

function exceptionMessage (exc) {
    return '[' + exc.constructor.name + '] ' + exc.message.slice (0, 500);
}

function exitScript () {
    process.exit (0);
}

function getExchangeProp (exchange, prop, defaultValue = undefined) {
    return (prop in exchange) ? exchange[prop] : defaultValue;
}

function setExchangeProp (exchange, prop, value) {
    exchange[prop] = value;
}

function initExchange (exchangeId, args) {
    return new (ccxt)[exchangeId] (args);
}

async function importTestFile (filePath) {
    // eslint-disable-next-line global-require, import/no-dynamic-require, no-path-concat
    return (await import (pathToFileURL (filePath + '.js') as any) as any)['default'];
}

async function setTestFiles (holderClass, properties) {
    // exchange tests
    for (let i = 0; i < properties.length; i++) {
        const name = properties[i];
        const filePathWoExt = __dirname + '/Exchange/test.' + name;
        if (ioFileExists (filePathWoExt + '.' + ext)) {
            // eslint-disable-next-line global-require, import/no-dynamic-require, no-path-concat
            holderClass.testFiles[name] = await importTestFile (filePathWoExt);
        }
    }
    // errors tests
    const errorHierarchyKeys = Object.keys (errorsHierarchy);
    for (let i = 0; i < errorHierarchyKeys.length; i++) {
        const name = errorHierarchyKeys[i];
        const filePathWoExt = __dirname + '/base/errors/test.' + name;
        if (ioFileExists (filePathWoExt + '.' + ext)) {
            // eslint-disable-next-line global-require, import/no-dynamic-require, no-path-concat
            holderClass.testFiles[name] = await importTestFile (filePathWoExt);
        }
    }
}

async function close (exchange) {
    // stub
}

// *********************************
// ***** AUTO-TRANSPILER-START *****

export default class testMainClass extends baseMainTestClass {
    parseCliArgs () {
        this.info = getCliArgValue ('--info');
        this.verbose = getCliArgValue ('--verbose');
        this.debug = getCliArgValue ('--debug');
        this.privateTest = getCliArgValue ('--private');
        this.privateTestOnly = getCliArgValue ('--privateOnly');
        this.sandbox = getCliArgValue ('--sandbox');
    }

    async init (exchangeId, symbol) {
        this.parseCliArgs ();
        const symbolStr = symbol !== undefined ? symbol : 'all';
        console.log ('\nTESTING ', ext, { 'exchange': exchangeId, 'symbol': symbolStr }, '\n');
        const exchangeArgs = {
            'verbose': this.verbose,
            'debug': this.debug,
            'enableRateLimit': true,
            'timeout': 30000,
        };
        const exchange = initExchange (exchangeId, exchangeArgs);
        await this.importFiles (exchange);
        this.expandSettings (exchange, symbol);
        await this.startTest (exchange, symbol);
        await close (exchange);
    }

    async importFiles (exchange) {
        // exchange tests
        this.testFiles = {};
        const properties = Object.keys (exchange.has);
        properties.push ('loadMarkets');
        await setTestFiles (this, properties);
    }

    expandSettings (exchange, symbol) {
        const exchangeId = exchange.id;
        const keysGlobal = rootDir + 'keys.json';
        const keysLocal = rootDir + 'keys.local.json';
        const keysGlobalExists = ioFileExists (keysGlobal);
        const keysLocalExists = ioFileExists (keysLocal);
        const globalSettings = keysGlobalExists ? ioFileRead (keysGlobal) : {};
        const localSettings = keysLocalExists ? ioFileRead (keysLocal) : {};
        const allSettings = exchange.deepExtend (globalSettings, localSettings);
        const exchangeSettings = exchange.safeValue (allSettings, exchangeId, {});
        if (exchangeSettings) {
            const settingKeys = Object.keys (exchangeSettings);
            for (let i = 0; i < settingKeys.length; i++) {
                const key = settingKeys[i];
                if (exchangeSettings[key]) {
                    let finalValue = undefined;
                    if (typeof exchangeSettings[key] === 'object') {
                        const existing = getExchangeProp (exchange, key, {});
                        finalValue = exchange.deepExtend (existing, exchangeSettings[key]);
                    } else {
                        finalValue = exchangeSettings[key];
                    }
                    setExchangeProp (exchange, key, finalValue);
                }
            }
        }
        // credentials
        const reqCreds = getExchangeProp (exchange, 're' + 'quiredCredentials'); // dont glue the r-e-q-u-i-r-e phrase, because leads to messed up transpilation
        const objkeys = Object.keys (reqCreds);
        for (let i = 0; i < objkeys.length; i++) {
            const credential = objkeys[i];
            const isRequired = reqCreds[credential];
            if (isRequired && getExchangeProp (exchange, credential) === undefined) {
                const fullKey = exchangeId + '_' + credential;
                const credentialEnvName = fullKey.toUpperCase (); // example: KRAKEN_APIKEY
                const credentialValue = (credentialEnvName in envVars) ? envVars[credentialEnvName] : undefined;
                if (credentialValue) {
                    setExchangeProp (exchange, credential, credentialValue);
                }
            }
        }
        // skipped tests
        const skippedFile = rootDirForSkips + 'skip-tests.json';
        const skippedSettings = ioFileRead (skippedFile);
        const skippedSettingsForExchange = exchange.safeValue (skippedSettings, exchangeId, {});
        // others
        const timeout = exchange.safeValue (skippedSettingsForExchange, 'timeout');
        if (timeout !== undefined) {
            exchange.timeout = timeout;
        }
        exchange.httpsProxy = exchange.safeString (skippedSettingsForExchange, 'httpsProxy');
        this.skippedMethods = exchange.safeValue (skippedSettingsForExchange, 'skipMethods', {});
        this.checkedPublicTests = {};
    }

    addPadding (message, size) {
        // has to be transpilable
        let res = '';
        const missingSpace = size - message.length - 0; // - 0 is added just to trick transpile to treat the .length as a string for php
        if (missingSpace > 0) {
            for (let i = 0; i < missingSpace; i++) {
                res += ' ';
            }
        }
        return message + res;
    }

    async testMethod (methodName, exchange, args, isPublic) {
        const methodNameInTest = getTestName (methodName);
        // if this is a private test, and the implementation was already tested in public, then no need to re-test it in private test (exception is fetchCurrencies, because our approach in base exchange)
        if (!isPublic && (methodNameInTest in this.checkedPublicTests) && (methodName !== 'fetchCurrencies')) {
            return;
        }
        let skipMessage = undefined;
        const isFetchOhlcvEmulated = (methodName === 'fetchOHLCV' && exchange.has['fetchOHLCV'] === 'emulated'); // todo: remove emulation from base
        if ((methodName !== 'loadMarkets') && (!(methodName in exchange.has) || !exchange.has[methodName]) || isFetchOhlcvEmulated) {
            skipMessage = '[INFO:UNSUPPORTED_TEST]'; // keep it aligned with the longest message
        } else if ((methodName in this.skippedMethods) && (typeof this.skippedMethods[methodName] === 'string')) {
            skipMessage = '[INFO:SKIPPED_TEST]';
        } else if (!(methodNameInTest in this.testFiles)) {
            skipMessage = '[INFO:UNIMPLEMENTED_TEST]';
        }
        if (skipMessage) {
            if (this.info) {
                dump (this.addPadding (skipMessage, 25), exchange.id, methodNameInTest);
            }
            return;
        }
        const argsStringified = '(' + args.join (',') + ')';
        if (this.info) {
            dump (this.addPadding ('[INFO:TESTING]', 25), exchange.id, methodNameInTest, argsStringified);
        }
        try {
            const skippedProperties = exchange.safeValue (this.skippedMethods, methodName, {});
            await callMethod (this.testFiles, methodNameInTest, exchange, skippedProperties, args);
            if (isPublic) {
                this.checkedPublicTests[methodNameInTest] = true;
            }
        } catch (e) {
            const isAuthError = (e instanceof AuthenticationError);
            // If public test faces authentication error, we don't break (see comments under `testSafe` method)
            if (isPublic && isAuthError) {
                if (this.info) {
                    dump ('[TEST_WARNING]', 'Authentication problem for public method', exceptionMessage (e), exchange.id, methodNameInTest, argsStringified);
                }
            } else {
                throw e;
            }
        }
    }

    async testSafe (methodName, exchange, args, isPublic) {
        // `testSafe` method does not throw an exception, instead mutes it.
        // The reason we mute the thrown exceptions here is because if this test is part
        // of "runPublicTests", then we don't want to stop the whole test if any single
        // test-method fails. For example, if "fetchOrderBook" public test fails, we still
        // want to run "fetchTickers" and other methods. However, independently this fact,
        // from those test-methods we still echo-out (console.log/print...) the exception
        // messages with specific formatted message "[TEST_FAILURE] ..." and that output is
        // then regex-parsed by run-tests.js, so the exceptions are still printed out to
        // console from there. So, even if some public tests fail, the script will continue
        // doing other things (testing other spot/swap or private tests ...)
        const maxRetries = 5;
        const argsStringified = '(' + args.join (',') + ')';
        for (let i = 0; i < maxRetries; i++) {
            try {
                await this.testMethod (methodName, exchange, args, isPublic);
                return true;
            } catch (e) {
                if (e instanceof OnMaintenance) {
                    dump ('[SKIPPED] Exchange is on maintenance', exchangeId);
                    exitScript ();
                }
                const isRateLimitExceeded = (e instanceof RateLimitExceeded);
                const isExchangeNotAvailable = (e instanceof ExchangeNotAvailable);
                const isNetworkError = (e instanceof NetworkError);
                const isDDoSProtection = (e instanceof DDoSProtection);
                const isRequestTimeout = (e instanceof RequestTimeout);
                const tempFailure = (isRateLimitExceeded || isExchangeNotAvailable || isNetworkError || isDDoSProtection || isRequestTimeout);
                if (tempFailure) {
                    // wait and retry again
                    await exchange.sleep (i * 1000); // increase wait seconds on every retry
                    continue;
                } else {
                    // if not temp failure, then dump exception without retrying
                    dump ('[TEST_WARNING]', 'Method could not be tested', exceptionMessage (e), exchange.id, methodName, argsStringified);
                    return false;
                }
            }
        }
        // if maxretries was gone with same `tempFailure` error, then let's eventually return false
        dump ('[TEST_WARNING]', 'Method could not be tested, because of temporary access issues', exchange.id, methodName, argsStringified);
        return false;
    }

    async runPublicTests (exchange, symbol) {
        const tests = {
            'loadMarkets': [],
            'fetchCurrencies': [],
            'fetchTicker': [ symbol ],
            'fetchTickers': [ symbol ],
            'fetchOHLCV': [ symbol ],
            'fetchTrades': [ symbol ],
            'fetchOrderBook': [ symbol ],
            'fetchL2OrderBook': [ symbol ],
            'fetchOrderBooks': [],
            'fetchBidsAsks': [],
            'fetchStatus': [],
            'fetchTime': [],
        };
        const market = exchange.market (symbol);
        const isSpot = market['spot'];
        if (isSpot) {
            tests['fetchCurrencies'] = [];
        } else {
            tests['fetchFundingRates'] = [ symbol ];
            tests['fetchFundingRate'] = [ symbol ];
            tests['fetchFundingRateHistory'] = [ symbol ];
            tests['fetchIndexOHLCV'] = [ symbol ];
            tests['fetchMarkOHLCV'] = [ symbol ];
            tests['fetchPremiumIndexOHLCV'] = [ symbol ];
        }
        this.publicTests = tests;
        const testNames = Object.keys (tests);
        const promises = [];
        for (let i = 0; i < testNames.length; i++) {
            const testName = testNames[i];
            const testArgs = tests[testName];
            promises.push (this.testSafe (testName, exchange, testArgs, true));
        }
        // promises.push (testThrottle ()); // todo - not yet ready in other langs too
        const results = await Promise.all (promises);
        const errors = [];
        for (let i = 0; i < testNames.length; i++) {
            if (!results[i]) {
                errors.push (testNames[i]);
            }
        }
        if (this.info) {
            // we don't throw exception for public-tests, see comments under 'testSafe' method
            let failedMsg = '';
            if (errors.length) {
                failedMsg = ' | Failed methods: ' + errors.join (', ');
            }
            dump (this.addPadding ('[INFO:PUBLIC_TESTS_DONE]' + market['type'] + failedMsg, 25), exchange.id);
        }
    }

    async loadExchange (exchange) {
        await exchange.loadMarkets ();
        assert (typeof exchange.markets === 'object', '.markets is not an object');
        assert (Array.isArray (exchange.symbols), '.symbols is not an array');
        const symbolsLength = exchange.symbols.length;
        const marketKeys = Object.keys (exchange.markets);
        const marketKeysLength = marketKeys.length;
        assert (symbolsLength > 0, '.symbols count <= 0 (less than or equal to zero)');
        assert (marketKeysLength > 0, '.markets objects keys length <= 0 (less than or equal to zero)');
        assert (symbolsLength === marketKeysLength, 'number of .symbols is not equal to the number of .markets');
        const symbols = [
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
        ];
        const resultSymbols = [];
        const exchangeSpecificSymbols = exchange.symbols;
        for (let i = 0; i < exchangeSpecificSymbols.length; i++) {
            const symbol = exchangeSpecificSymbols[i];
            if (exchange.inArray (symbol, symbols)) {
                resultSymbols.push (symbol);
            }
        }
        let resultMsg = '';
        const resultLength = resultSymbols.length;
        const exchangeSymbolsLength = exchange.symbols.length;
        if (resultLength > 0) {
            if (exchangeSymbolsLength > resultLength) {
                resultMsg = resultSymbols.join (', ') + ' + more...';
            } else {
                resultMsg = resultSymbols.join (', ');
            }
        }
        dump ('Exchange loaded', exchangeSymbolsLength, 'symbols', resultMsg);
    }

    getTestSymbol (exchange, isSpot, symbols) {
        let symbol = undefined;
        for (let i = 0; i < symbols.length; i++) {
            const s = symbols[i];
            const market = exchange.safeValue (exchange.markets, s);
            if (market !== undefined) {
                const active = exchange.safeValue (market, 'active');
                if (active || (active === undefined)) {
                    symbol = s;
                    break;
                }
            }
        }
        return symbol;
    }

    getExchangeCode (exchange, codes = undefined) {
        if (codes === undefined) {
            codes = [ 'BTC', 'ETH', 'XRP', 'LTC', 'BCH', 'EOS', 'BNB', 'BSV', 'USDT' ];
        }
        const code = codes[0];
        for (let i = 0; i < codes.length; i++) {
            if (codes[i] in exchange.currencies) {
                return codes[i];
            }
        }
        return code;
    }

    getMarketsFromExchange (exchange, spot = true) {
        const res = {};
        const markets = exchange.markets;
        const keys = Object.keys (markets);
        for (let i = 0; i < keys.length; i++) {
            const key = keys[i];
            const market = markets[key];
            if (spot && market['spot']) {
                res[market['symbol']] = market;
            } else if (!spot && !market['spot']) {
                res[market['symbol']] = market;
            }
        }
        return res;
    }

    getValidSymbol (exchange, spot = true) {
        const currentTypeMarkets = this.getMarketsFromExchange (exchange, spot);
        const codes = [
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
        ];
        const spotSymbols = [
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
        ];
        const swapSymbols = [
            'BTC/USDT:USDT',
            'BTC/USD:USD',
            'ETH/USDT:USDT',
            'ETH/USD:USD',
            'LTC/USDT:USDT',
            'DOGE/USDT:USDT',
            'ADA/USDT:USDT',
            'BTC/USD:BTC',
            'ETH/USD:ETH',
        ];
        const targetSymbols = spot ? spotSymbols : swapSymbols;
        let symbol = this.getTestSymbol (exchange, spot, targetSymbols);
        // if symbols wasn't found from above hardcoded list, then try to locate any symbol which has our target hardcoded 'base' code
        if (symbol === undefined) {
            for (let i = 0; i < codes.length; i++) {
                const currentCode = codes[i];
                const marketsArrayForCurrentCode = exchange.filterBy (currentTypeMarkets, 'base', currentCode);
                const indexedMkts = exchange.indexBy (marketsArrayForCurrentCode, 'symbol');
                const symbolsArrayForCurrentCode = Object.keys (indexedMkts);
                const symbolsLength = symbolsArrayForCurrentCode.length;
                if (symbolsLength) {
                    symbol = this.getTestSymbol (exchange, spot, symbolsArrayForCurrentCode);
                    break;
                }
            }
        }
        // if there wasn't found any symbol with our hardcoded 'base' code, then just try to find symbols that are 'active'
        if (symbol === undefined) {
            const activeMarkets = exchange.filterBy (currentTypeMarkets, 'active', true);
            const activeSymbols = [];
            for (let i = 0; i < activeMarkets.length; i++) {
                activeSymbols.push (activeMarkets[i]['symbol']);
            }
            symbol = this.getTestSymbol (exchange, spot, activeSymbols);
        }
        if (symbol === undefined) {
            const values = Object.values (currentTypeMarkets);
            const valuesLength = values.length;
            if (valuesLength > 0) {
                const first = values[0];
                if (first !== undefined) {
                    symbol = first['symbol'];
                }
            }
        }
        return symbol;
    }

    async testExchange (exchange, providedSymbol = undefined) {
        let spotSymbol = undefined;
        let swapSymbol = undefined;
        if (providedSymbol !== undefined) {
            const market = exchange.market (providedSymbol);
            if (market['spot']) {
                spotSymbol = providedSymbol;
            } else {
                swapSymbol = providedSymbol;
            }
        } else {
            if (exchange.has['spot']) {
                spotSymbol = this.getValidSymbol (exchange, true);
            }
            if (exchange.has['swap']) {
                swapSymbol = this.getValidSymbol (exchange, false);
            }
        }
        if (spotSymbol !== undefined) {
            dump ('Selected SPOT SYMBOL:', spotSymbol);
        }
        if (swapSymbol !== undefined) {
            dump ('Selected SWAP SYMBOL:', swapSymbol);
        }
        if (!this.privateTestOnly) {
            if (exchange.has['spot'] && spotSymbol !== undefined) {
                if (this.info) {
                    dump ('[INFO:SPOT TESTS]');
                }
                exchange.options['type'] = 'spot';
                await this.runPublicTests (exchange, spotSymbol);
            }
            if (exchange.has['swap'] && swapSymbol !== undefined) {
                if (this.info) {
                    dump ('[INFO:SWAP TESTS]');
                }
                exchange.options['type'] = 'swap';
                await this.runPublicTests (exchange, swapSymbol);
            }
        }
        if (this.privateTest || this.privateTestOnly) {
            if (exchange.has['spot'] && spotSymbol !== undefined) {
                exchange.options['defaultType'] = 'spot';
                await this.runPrivateTests (exchange, spotSymbol);
            }
            if (exchange.has['swap'] && swapSymbol !== undefined) {
                exchange.options['defaultType'] = 'swap';
                await this.runPrivateTests (exchange, swapSymbol);
            }
        }
    }

    async runPrivateTests (exchange, symbol) {
        if (!exchange.checkRequiredCredentials (false)) {
            dump ('[Skipping private tests]', 'Keys not found');
            return;
        }
        const code = this.getExchangeCode (exchange);
        // if (exchange.extendedTest) {
        //     await test ('InvalidNonce', exchange, symbol);
        //     await test ('OrderNotFound', exchange, symbol);
        //     await test ('InvalidOrder', exchange, symbol);
        //     await test ('InsufficientFunds', exchange, symbol, balance); // danger zone - won't execute with non-empty balance
        // }
        const tests = {
            'signIn': [ ],
            'fetchBalance': [ ],
            'fetchAccounts': [ ],
            'fetchTransactionFees': [ ],
            'fetchTradingFees': [ ],
            'fetchStatus': [ ],
            'fetchOrders': [ symbol ],
            'fetchOpenOrders': [ symbol ],
            'fetchClosedOrders': [ symbol ],
            'fetchMyTrades': [ symbol ],
            'fetchLeverageTiers': [ symbol ],
            'fetchLedger': [ code ],
            'fetchTransactions': [ code ],
            'fetchDeposits': [ code ],
            'fetchWithdrawals': [ code ],
            'fetchBorrowRates': [ code ],
            'fetchBorrowRate': [ code ],
            'fetchBorrowInterest': [ code, symbol ],
            'addMargin': [ symbol ],
            'reduceMargin': [ symbol ],
            'setMargin': [ symbol ],
            'setMarginMode': [ symbol ],
            'setLeverage': [ symbol ],
            'cancelAllOrders': [ symbol ],
            'cancelOrder': [ symbol ],
            'cancelOrders': [ symbol ],
            'fetchCanceledOrders': [ symbol ],
            'fetchClosedOrder': [ symbol ],
            'fetchOpenOrder': [ symbol ],
            'fetchOrder': [ symbol ],
            'fetchOrderTrades': [ symbol ],
            'fetchPosition': [ symbol ],
            'fetchDeposit': [ code ],
            'createDepositAddress': [ code ],
            'fetchDepositAddress': [ code ],
            'fetchDepositAddresses': [ code ],
            'fetchDepositAddressesByNetwork': [ code ],
            'editOrder': [ symbol ],
            'fetchBorrowRateHistory': [ symbol ],
            'fetchBorrowRatesPerSymbol': [ symbol ],
            'fetchLedgerEntry': [ code ],
            'fetchWithdrawal': [ code ],
            'transfer': [ code ],
            'withdraw': [ code ],
        };
        const market = exchange.market (symbol);
        const isSpot = market['spot'];
        if (isSpot) {
            tests['fetchCurrencies'] = [ symbol ];
        } else {
            // derivatives only
            tests['fetchPositions'] = [ symbol ]; // this test fetches all positions for 1 symbol
            tests['fetchPosition'] = [ symbol ];
            tests['fetchPositionRisk'] = [ symbol ];
            tests['setPositionMode'] = [ symbol ];
            tests['setMarginMode'] = [ symbol ];
            tests['fetchOpenInterestHistory'] = [ symbol ];
            tests['fetchFundingRateHistory'] = [ symbol ];
            tests['fetchFundingHistory'] = [ symbol ];
        }
        const combinedPublicPrivateTests = exchange.deepExtend (this.publicTests, tests);
        const testNames = Object.keys (combinedPublicPrivateTests);
        const promises = [];
        for (let i = 0; i < testNames.length; i++) {
            const testName = testNames[i];
            const testArgs = combinedPublicPrivateTests[testName];
            promises.push (this.testSafe (testName, exchange, testArgs, false));
        }
        const results = await Promise.all (promises);
        const errors = [];
        for (let i = 0; i < testNames.length; i++) {
            const testName = testNames[i];
            const success = results[i];
            if (!success) {
                errors.push (testName);
            }
        }
        const errorsCnt = errors.length; // PHP transpile count($errors)
        if (errorsCnt > 0) {
            throw new Error ('Failed private tests [' + market['type'] + ']: ' + errors.join (', '));
        } else {
            if (this.info) {
                dump (this.addPadding ('[INFO:PRIVATE_TESTS_DONE]', 25), exchange.id);
            }
        }
    }

    async startTest (exchange, symbol) {
        // we don't need to test aliases
        if (exchange.alias) {
            return;
        }
        if (this.sandbox || getExchangeProp (exchange, 'sandbox')) {
            exchange.setSandboxMode (true);
        }
        await this.loadExchange (exchange);
        await this.testExchange (exchange, symbol);
    }
}
// ***** AUTO-TRANSPILER-END *****
// *******************************
(new testMainClass ()).init (exchangeId, exchangeSymbol);
