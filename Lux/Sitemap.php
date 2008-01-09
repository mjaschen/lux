<?php
/**
 *
 * Support class to generate sitemaps using the Google Sitemap Protocol.
 *
 * Examples of usage:
 *
 * {{code: php
 *
 *     // Create a new sitemap with 3 pages.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->setPage('http://www.example.com/');
 *     $sitemap->setPage('http://www.example.com/foo');
 *     $sitemap->setPage('http://www.example.com/bar');
 *     $sitemap->save('/path/to/sitemap1.xml.gz');
 *
 *     // Update a sitemap: add more pages or update existing ones.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->load('/path/to/sitemap1.xml.gz');
 *     $sitemap->setPage('http://www.example.com/foo', '2008-01-03', 'daily',
 *         '0.9');
 *     $sitemap->setPage('http://www.example.com/ding');
 *     $sitemap->save();
 *
 *     // Update a sitemap: delete a page.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->load('/path/to/sitemap1.xml.gz');
 *     $sitemap->delete('http://www.example.com/');
 *     $sitemap->save();
 *
 *     // Basically the same API is used to create sitemap indexes:
 *
 *     // Create a new sitemap index with 3 sitemaps.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->setSitemap('http://www.example.com/sitemap1.xml.gz');
 *     $sitemap->setSitemap('http://www.example.com/sitemap2.xml.gz');
 *     $sitemap->setSitemap('http://www.example.com/sitemap3.xml.gz');
 *     $sitemap->save('/path/to/sitemap.xml');
 *
 *     // Update a sitemap index: add more sitemaps or update existing ones.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->load('/path/to/sitemap.xml');
 *     $sitemap->setSitemap('http://www.example.com/sitemap1.xml.gz',
 *         '2008-01-03');
 *     $sitemap->setSitemap('http://www.example.com/sitemap4.xml.gz');
 *     $sitemap->save();
 *
 *     // Update a sitemap index: delete a sitemap.
 *     $sitemap = Solar::factory('Lux_Sitemap');
 *     $sitemap->load('/path/to/sitemap.xml');
 *     $sitemap->delete('http://www.example.com/sitemap1.xml.gz');
 *     $sitemap->save();
 *
 * }}
 *
 * Sample XML Sitemap:
 *
 * {{code: xml
 *     <?xml version="1.0" encoding="UTF-8"?>
 *     <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
 *       <url>
 *         <loc>http://www.example.com/</loc>
 *           <lastmod>2005-01-01</lastmod>
 *           <changefreq>daily</changefreq>
 *           <priority>0.8</priority>
 *       </url>
 *       <url>
 *         <loc>http://www.example.com/foo</loc>
 *           <lastmod>2004-10-01T18:23:17+00:00</lastmod>
 *           <changefreq>monthly</changefreq>
 *           <priority>0.5</priority>
 *       </url>
 *     </urlset>
 * }}
 *
 * Sample XML Sitemap Index:
 *
 * {{code: xml
 *     <?xml version="1.0" encoding="UTF-8"?>
 *     <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
 *       <sitemap>
 *         <loc>http://www.example.com/sitemap1.xml.gz</loc>
 *         <lastmod>2004-10-01T18:23:17+00:00</lastmod>
 *       </sitemap>
 *       <sitemap>
 *         <loc>http://www.example.com/sitemap2.xml.gz</loc>
 *         <lastmod>2005-01-01</lastmod>
 *       </sitemap>
 *     </sitemapindex>
 * }}
 *
 * @category Lux
 *
 * @package Lux_Sitemap
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @version $Id$
 *
 */
class Lux_Sitemap extends Solar_Base
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `path`
     * : (string) Default path to save the sitemap.
     *
     * `file`
     * : (string) Default name for the sitemap file. If the extension is '.gz',
     *   the sitemap will be saved using compression.
     *
     * `ping`
     * : (bool) If true, ping services automatically on save(). Useful for
     *   single sitemap files - when using sitemap indexes, only the index
     *   should be submitted, so call ping() separatelly.
     *
     * `ping_base_url`
     * : (string) Url to the directory where the sitemap is located,
     *   usually the site root url. Used to build the full url to the sitemap
     *   and ping services.
     *
     * `ping_urls`
     * : (array) List of service url's to ping about the sitemap, formatted for
     *   sprintf(). A placeholder %s is replaced by the sitemap url.
     *
     * `http`
     * : (array) Solar_Http_Request configuration, use to ping services.
     *
     * @var array
     *
     */
    protected $_Lux_Sitemap = array(
        'path'          => null,
        'file'          => 'sitemap.xml.gz',
        'ping'          => false,
        'ping_base_url' => null,
        'ping_urls'     => array(
            'http://www.google.com/webmasters/tools/ping?sitemap=%s',
        ),
        'http'          => null,
    );

    /**
     *
     * The XML document for the sitemap.
     *
     * @var DOMDocument
     *
     */
    protected $_doc;

    /**
     *
     * The root element for the sitemap.
     *
     * @var DOMNode
     *
     */
    protected $_root;

    /**
     *
     * A XPath object to make sitemap queries.
     *
     * @var DOMXPath
     *
     */
    protected $_xpath;

    /**
     *
     * Sitemap type: 'urlset' or 'sitemapindex'.
     *
     * @var string
     *
     */
    protected $_type;

    /**
     *
     * Full path to the current sitemap file or the default path if no file was
     * loaded.
     *
     * @var string
     *
     */
    protected $_file;

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Do parent construction.
        parent::__construct($config);

        // Set default sitemap file path.
        if (! $this->_config['path']) {
            $this->_config['path'] = Solar_Dir::tmp();
        }

        $path = Solar_Dir::fix($this->_config['path']);
        $this->_file = $path . $this->_config['file'];
    }

    /**
     *
     * Loads a XML sitemap.
     *
     * @param string $file Path to the XML file.
     *
     */
    public function load($file)
    {
        if (! is_readable($file)) {
            throw $this->_exception('ERR_FILE_NOT_READABLE', array(
                'file' => $file,
            ));
        }

        // Set current file status.
        $this->_file = $file;

        // Create a new XML.
        $this->_doc = new DOMDocument('1.0', 'UTF-8');

        // If extension is '.gz', turn on compression.
        if (substr($file, -3) == '.gz') {
            $gzip = true;
        } else {
            $gzip = false;
        }

        if ($gzip) {
            // Load XML from compressed file.
            // Get the file size.
            $handle = fopen($file, 'rb');
            fseek($handle, -4, SEEK_END);
            $buffer = unpack('V', fread($handle, 4));
            $file_size = end($buffer);
            fclose($handle);

            // Get the XML data.
            $handle = gzopen($file, 'rb');
            $data = gzread($handle, $file_size);
            gzclose($handle);

            $this->_doc->loadXML($data);
        } else {
            // Load XML from non-compressed file.
            $this->_doc->load($file);
        }

        // Register XPath for queries.
        $this->_setXpath();

        // Guess the sitemap type depending on the root node name.
        $nodes = $this->_xpath->query('/prefix:urlset | /prefix:sitemapindex');

        if ($nodes->length == 1) {
            $ns = $nodes->item(0)->getAttribute('xmlns');
            if ($ns != 'http://www.sitemaps.org/schemas/sitemap/0.9') {
                throw $this->_exception('ERR_XML_INVALID');
            }

            // Found and valid. Set root and type.
            $this->_root = $nodes->item(0);
            $this->_type = $this->_root->nodeName;
        } else {
            throw $this->_exception('ERR_XML_INVALID');
        }
    }

    /**
     *
     * Saves the sitemap as XML, compressed or not.
     *
     * @param string $file Destination sitemap file, including path. If the
     * filename ends with '.gz', it will be saved using compression.
     *
     * @return void|array If automatic ping is enabled, return the results from
     * ping().
     *
     */
    public function save($file = null)
    {
        if (! $this->_doc || !$this->_root) {
            throw $this->_exception('ERR_XML_INVALID');
        }

        if (! $file) {
            // Use default or current file.
            $file = $this->_file;
        } else {
            // Set current file.
            $this->_file = $file;
        }

        // Check if the file or dir has write permissions.
        if ((file_exists($file) && !is_writable($file)) ||
            (! file_exists($file) && !is_writable(dirname($file)))) {
            throw $this->_exception('ERR_FILE_NOT_WRITABLE', array(
                'file' => $file,
            ));
        }

        // Set some common doc format options.
        $this->_doc->formatOutput = true;
        $this->_doc->preserveWhiteSpace = false;

        if (substr($file, -3) == '.gz') {
            // Save compressed.
            $xml = $this->_doc->saveXML();
            $data = gzencode($xml, 9);
            file_put_contents($file, $data);
        } else {
            $this->_doc->save($file);
        }

        if ($this->_config['ping']) {
            // Ping services.
            $url = rtrim($this->_config['ping_base_url'], '/');
            $sitemap_url = $url . '/' . basename($file);
            return $this->ping($sitemap_url);
        }
    }

    /**
     *
     * Deletes a node from the current sitemap.
     *
     * @param string $url Url of the page or sitemap.
     *
     * @return bool True if the node was found, false otherwise.
     *
     */
    public function delete($url)
    {
        if (! $this->_doc || !$this->_root) {
            throw $this->_exception('ERR_XML_INVALID');
        }

        $node = $this->_fetch($url);

        if ($node) {
            $node->parentNode->removeChild($node);
            return true;
        }

        return false;
    }

    /**
     *
     * Inserts or updates a page in the sitemap.
     *
     * @param string $url Full URL of the page. If it already exists, the entry
     * will be updated, otherwise it will be inserted.
     *
     * @param string $lastmod Last modification time.
     *
     * @param string $changefreq How frequently the page is likely to change.
     * Valid values are:
     *
     *     always
     *     hourly
     *     daily
     *     weekly
     *     monthly
     *     yearly
     *     never
     *
     * @param string $priority The priority of this URL relative to other URLs
     * on your site. Valid values range from 0.0 to 1.0.
     *
     */
    public function setPage($url, $lastmod = null, $changefreq = 'monthly',
        $priority = '0.5')
    {
        if ($this->_type == 'sitemapindex') {
            // Sitemap was already started and is not a urlset.
            throw $this->_exception('ERR_WRONG_SITEMAP_TYPE');
        }

        $values = array(
            'loc'        => htmlentities($url),
            'lastmod'    => $lastmod ? $lastmod : date('Y-m-d'),
            'changefreq' => $changefreq,
            'priority'   => $priority,
        );

        if (! $this->_doc || !$this->_root) {
            // Create a new document.
            $this->_createDoc('urlset');
            $node = null;
        } else {
            // Check if the node already exists based on the url.
            $node = $this->_fetch($url);
        }

        $this->_setEntry($values, $node);
    }

    /**
     *
     * Inserts or updates a sitemap in the sitemap index.
     *
     * @param string $url Full URL of the sitemap. If it already exists, the
     * entry will be updated, otherwise it will be inserted.
     *
     * @param string $lastmod Last modification time.
     *
     */
    public function setSitemap($url, $lastmod = null)
    {
        if ($this->_type == 'urlset') {
            // Sitemap was already started and is not a sitemapindex.
            throw $this->_exception('ERR_WRONG_SITEMAP_TYPE');
        }

        $values = array(
            'loc'     => htmlentities($url),
            'lastmod' => $lastmod ? $lastmod : date('Y-m-d'),
        );

        if (! $this->_doc || !$this->_root) {
            // Create a new document.
            $this->_createDoc('sitemapindex');
            $node = null;
        } else {
            // Check if the node already exists based on the url.
            $node = $this->_fetch($url);
        }

        $this->_setEntry($values, $node);
    }

    /**
     *
     * Pings services to tell them about a new/updated sitemap.
     *
     * @var string $sitemap_url The full sitemap url to be ping'ed.
     *
     * @var array $ping_urls List of service url's to ping, formatted for
     * sprintf(). A placeholder (%s) will be replaced by the sitemap url.
     *
     * @return array $res A list of the service url's mapping to the the status
     * code they returned.
     *
     */
    public function ping($sitemap_url, $ping_urls = null)
    {
        $res = array();

        if (! $ping_urls) {
            $ping_urls = $this->_config['ping_urls'];
        }

        $request = Solar::factory('Solar_Http_Request', $this->_config['http']);

        foreach ((array) $ping_urls as $ping_url) {
            // Build the uri.
            $uri = sprintf($ping_url, $sitemap_url);

            // Ping!
            $response = $request->setUri($uri)->setMethod('head')->fetch();
            $res[$uri] = $response->getStatusCode();
        }

        return $res;
    }

    /**
     *
     * Inserts or updates a 'url' or 'sitemap' entry. Used internally only.
     *
     * @param array $values List of node-keys mapping to node-values.
     * Valid sitemap keys are 'loc', 'lastmod', 'changefreq' and 'priority'.
     * Valid sitemap index keys are 'loc' and 'lastmod'.
     *
     * @param DOMNode $node Parent node ('url' or 'sitemap'), passed only when
     * the entry is being updated.
     *
     */
    protected function _setEntry($values, $node = null)
    {
        if (! $node) {
            // New sitemap entry.
            $type = $this->_type == 'urlset' ? 'url' : 'sitemap';
            $temp = $this->_doc->createElement($type);
            $node = $this->_root->appendChild($temp);
            $update = false;
        } else {
            // Existing sitemap entry.
            $update = true;
        }

        foreach ($values as $key => $value) {
            $new_node = $this->_doc->createElement($key, $value);

            if ($update) {
                // Check if the node already exists.
                $nodes = $node->getElementsByTagName($key);
                $old_node = $nodes->item(0);

                if ($old_node) {
                    // Node exists, so replace it.
                    $node->replaceChild($new_node, $old_node);
                    return;
                }
            }

            $node->appendChild($new_node);
        }
    }

    /**
     *
     * Creates a new DOMDocument for a sitemap. Used internally only.
     *
     * @param string $type Type of the sitemap: 'urlset' or 'sitemapindex'.
     *
     */
    protected function _createDoc($type = 'urlset')
    {
        $this->_type = $type != 'urlset' ? 'sitemapindex' : 'urlset';

        // Create new document.
        $this->_doc = new DOMDocument('1.0', 'UTF-8');

        // Create root node.
        $node = $this->_doc->createElement($this->_type);
        $node->setAttribute('xmlns',
            'http://www.sitemaps.org/schemas/sitemap/0.9');

        $this->_root = $this->_doc->appendChild($node);

        // Register XPath for queries.
        $this->_setXpath();
    }

    /**
     *
     * Creates a XPath object for sitemap queries.
     *
     */
    protected function _setXpath()
    {
        $this->_xpath = new DOMXPath($this->_doc);

        // Add a fake prefix because of the namespaced root node,
        // or queries won't work.
        $this->_xpath->registerNameSpace('prefix',
            'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     *
     * Fetches a node (<url> or <sitemap>) from the current sitemap.
     *
     * @param string $url Url of the page or sitemap.
     *
     * @return DOMNode|null The url/sitemap node if found, or null.
     *
     */
    protected function _fetch($url)
    {
        $query = '//prefix:loc[. = "' . htmlentities($url) . '"]';
        $nodes = $this->_xpath->query($query);
        $node = $nodes->item(0);

        return $node ? $node->parentNode : null;
    }
}