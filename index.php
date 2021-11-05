<?php

/**
 * Plugin Name:     NFT Json
 * Description:     Generate JSON for NFT, how it work ? just run to your CLI with command "php index.php"
 * Version:         1.0.0
 * Author:          Hengky ST
 */

final class NFT_Generate 
{
    /**
     * NFT_generate::$baseUri
     *
     * Base Uri for NFT.
     * Set your default base uri from here
     *
     * @access  private
     * @type    string
     */
    private string $baseUri = "ipfs://f7ff9e8b7bb2e09b70935a5d785e0cc5d9d0abf0";

    /**
     * NFT_generate::$prefix
     *
     * prefix image name.
     * Set your prefix image name from here
     *
     * @access  private
     * @type    integer
     */
    private string $prefix = "yocorp-";

    /**
     * NFT_generate::$suffix
     *
     * suffix image name.
     * Set your suffix image name from here
     *
     * @access  private
     * @type    integer
     */
    private string $suffix = "";

    /**
     * NFT_generate::$compiler
     *
     * compiler name.
     * Set your default compiler name from here
     *
     * @access  private
     * @type    integer
     */
    private string $description = "This is description, descript what you want";
    
    /**
     * NFT_generate::$artist
     *
     * artist name.
     * Set your default artist name from here
     *
     * @access  private
     * @type    integer
     */
    private string $artist = "Vanessa Angel";
    
    /**
     * NFT_generate::$compiler
     *
     * compiler name.
     * Set your default compiler name from here
     *
     * @access  private
     * @type    integer
     */
    private string $compiler = "Bot NFT json Generator";

    /**
     * NFT_generate::$clear_folder
     *
     * Clear uploads folder after generate.
     * Set true / false
     * Default is false
     *
     * @access  private
     * @type    integer
     */
    private bool $clear_folder = true;

    public function __construct()
    {
        // $this->jpg_to_png();die;
        // $this->scan_images('uploads');
        // generate
        $result = $this->generate();

        // get result
        if ($result) {
            print("\e[1;32m[Success] \e[0m File has been generate on folder " . $result . "\xA");
            $this->clear_folder();
        } else { 
            print("\e[0;31m[Failed] \e[0m Failed generate, check variable type then try again or call developer." . "\xA");
        }
    }

    protected function generate() : bool
    {
        if(empty($this->baseUri) || !file_exists('./uploads') || $this->is_dir_empty('./uploads')) return false;

        // create the folder
        $folder = $this->create_folder();

        if ($handle = opendir('./uploads')) {
            while (false !== ($entry = readdir($handle))) $files[] = $entry;
            $images = preg_grep('/\.(jpg|jpeg)(?:[\?\#].*)?$/i', $files);
        
            // generate file
            $index = 1;
            $_metadata = array();
            foreach($images as $image) {
                $filename = $this->prefix . $index . $this->suffix;
                $png = $this->jpg_to_png("./uploads/{$image}", "{$folder}/images", $filename);

                // skip if convert png is failed
                if (!file_exists("{$folder}/images/{$png}")) continue;

                $metadata = $this->get_format(array(
                    "dna"           => $this->create_dna($filename),
                    "name"          => '#' . $filename,
                    "description"   => $this->description,
                    "image"         => "{$this->baseUri}/{$png}",
                    "edition"       => $index,
                    "artist"        => $this->artist,
                ));

                $fp = fopen("{$folder}/json/{$index}.json", 'w');
                fwrite($fp, json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                fclose($fp);

                $_metadata[] = $metadata;
                $index++;
            }

            closedir($handle);
        }

        $fp = fopen("{$folder}/json/_metadata.json", 'w');
        fwrite($fp, json_encode($_metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fclose($fp);

        return $folder;
    }

    protected function create_folder() : string
    {
        $gen_folder = "./generate/";
        $base_folder = $gen_folder . 'build-' . date('d-m-Y-H-i-s');
        
        if (!file_exists($gen_folder)) {
            mkdir($gen_folder, 0755, true);
        }
        
        if (!file_exists($base_folder)) {
            mkdir($base_folder, 0755, true);
            mkdir($base_folder . '/json', 0755, true);
            mkdir($base_folder . '/images', 0755, true);
            return $base_folder;
        }

        return false;
    }

    protected function create_dna($name) : string
    {
        return sha1($name . strtotime(date('Y-m-d H:i:s')));
    }

    private function get_format($data) : array
    {
        return array(
            "dna"           => $data['dna'],
            "name"          => $data['name'],
            "description"   => $data['description'],
            "image"         => $data['image'],
            "edition"       => $data['edition'],
            "artist"        => $data['artist'],
            "date"          => strtotime(date('Y-m-d H:i:s')),
            "attributes"    => array(),
            "compiler"      => $this->compiler
        );
    } 

    private function jpg_to_png($image, $directory, $name)
    {
        $png = "{$name}.png";
        imagepng(imagecreatefromstring(file_get_contents($image)), "{$directory}/$png");

        return $png;
    }

    private function is_dir_empty($directory) {
        if (!is_readable($directory)) return null; 
        return (count(scandir($directory)) == 2);
    }

    private function clear_folder()
    {
        if (!$this->clear_folder) return false;

        array_map('unlink', array_filter(
            (array) array_merge(glob("./uploads/*"))));
    }
}

new NFT_Generate();