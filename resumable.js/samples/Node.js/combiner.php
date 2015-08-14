<?php
	/**
 *
 * Check if all the parts exist, and
 * gather all the parts of the file together
 * @param string $temp_dir - the temporary directory holding all the parts of the file
 * @param string $fileName - the original file name
 * @param string $chunkSize - each chunk size (in bytes)
 * @param string $totalSize - original file size (in bytes)
 */


function time_elapsed_A($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
        );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;

    return join(' ', $ret);
    }

 $oldtime = time();
function createFileFromChunks($temp_dir, $fileName, $uniqueIdentifier, $chunkSize, $totalSize) {
    echo "creating file\n";
	echo "\t$fileName in $temp_dir\n";
	echo "\tUnique id: $uniqueIdentifier\n";
	echo "\tchunkSize = $chunkSize, totalSize = $totalSize\n";
    // count all the parts of this file
    $total_files = 0;
    foreach(scandir($temp_dir) as $file) {
        if (stripos($file, str_replace(".","",$fileName)) !== false) {
            $total_files++;
        }
    }

    // check that all the parts are present
    // the size of the last part is between chunkSize and 2*$chunkSize
    if ($total_files * $chunkSize >=  ($totalSize - $chunkSize + 1)) {
	echo "Passed first check\n";
	echo "Current directory is ".`pwd`."\n";
	//echo "Contents are: ".`ls -l`."\n";
        // create the final destination file
        if (($fp = fopen($fileName, 'w')) !== false) {
	    echo "writing to ".`pwd`."/temp/$fileName";
            for ($i=1; $i<=$total_files; $i++) {
                fwrite($fp, file_get_contents($temp_dir.'/'.$uniqueIdentifier.".$i"));
		unlink($temp_dir.'/'.$uniqueIdentifier.".$i");
                //_log('writing chunk '.$i);
		//echo "Reading chunk: $temp_dir/$uniqueIdentifier.$i";
            }
            fclose($fp);
        } else {
		echo "Can't make the destination file :(\n";
            //_log('cannot create the destination file');
            return false;
        }

        // rename the temporary directory (to avoid access from other
        // concurrent chunks uploads) and than delete it
    	//chmod($temp_dir, 0757);
        //if (rename($temp_dir, $temp_dir.'_UNUSED')) {
        //    rmdir($temp_dir.'_UNUSED');
        //} else {
        //    rmdir($temp_dir);
        //}
    }
    else {
	echo "Failed first check :(\n";
	echo "$total_files * $chunkSize >=  ($totalSize - $chunkSize + 1)\n";
	echo $total_files*$chunkSize." >= ".($totalSize - $chunkSize + 1)."\n";
    }

}
echo "Running!\n";
$arguments = $argv;
//$arguments[0] is file name, so you would use arguments[1] --> arguments[4]
//

createFileFromChunks($arguments[1], $arguments[2], $arguments[3], 1024*1*1024, $arguments[4]);
$nowtime = time();
echo "\ntime elapsed: ".time_elapsed_A($nowtime - $oldtime)."\n";
echo "\ndone yayyyyyyYYYYYYYYY";
//createFileFromChunks('/tmp/resumable.js', 'test1g.tmp', 1024*1*1024, 1000000);
// php ~/resumable_custom/resumable.js/samples/Node.js/combiner.php '/tmp/resumable.js' 'test1g.tmp' resumable-1073741824-test1gtmp
