<?php
/**
 * Module:      scoresheets.output.php
 * Description: This module copies the pdf of the scoresheets of a given entry from a
 * directory in which the pdfs are protected from direct access from the web (setup by
 * the .htaccess in root) to a directory which is write-enabled and then show this
 * temporary copy to the entrant. The name of the file in the temporary directory is
 * appended by an obfuscated to prevent unwelcomed users to guess the name of the file
 * and access it.

 * Updated for v2.1.10 to use a simple encrypt/decrypt key to obfuscate the file names
 * passed by the URL generated by the brewer_entries.sec.php script. This was necessary
 * due to requests from users to be able to use entry numbers and not exclusively judging
 * numbers (entry numbers are available and not random like judging numbers). See
 * https://github.com/geoffhumphrey/brewcompetitiononlineentry/issues/731.
 *
 */

require ('../paths.php');
require (CONFIG.'bootstrap.php');
require (LANG.'language.lang.php');

if (isset($_SESSION['loginUsername'])) {

	if (($_SESSION['brewerEmail'] != $_SESSION['loginUsername']) && ($_SESSION['userLevel'] > 1)) {
	  	echo sprintf("<html><head><title>%s</title></head><body>",$label_error);
  		echo sprintf("<p>%s</p>",$header_text_104);
	  	echo "</body></html>";
  		exit();
	}

   	else {

		// Decode the file names
		$get_real_file_name = urldecode($_GET['scoresheetfilename']);
		$scoresheet_file_name = deobfuscateURL($get_real_file_name,$encryption_key);

		// Get the directory name from URL if present
		if ($view == "default") $scoresheetfile = USER_DOCS.$scoresheet_file_name;
		else $scoresheetfile = USER_DOCS.DIRECTORY_SEPARATOR.$view.DIRECTORY_SEPARATOR.$scoresheet_file_name;

		// Decrypt the filename
		$get_random_file_name = urldecode($_GET['randomfilename']);
		$random_file_name = deobfuscateURL($get_random_file_name,$encryption_key);
		$scoresheet_random_file_relative = "user_temp/".$random_file_name;
		$scoresheet_random_file = USER_TEMP.$random_file_name;

		if (copy($scoresheetfile, $scoresheet_random_file)) {

			header('Content-Type: application/pdf');
			if (isset($_GET['download'])) header("Content-Disposition: attachment; filename=$scoresheet_file_name");
			else header('Content-Disposition: inline; filename="' . $scoresheet_file_name . '"');
			ob_clean();
			flush();
			readfile($scoresheet_random_file);

		}

		else {
			echo sprintf("<html><head><title>%s</title><meta http-equiv=\"refresh\" content=\"0;URL='".$base_url."index.php?section=list&msg=11'\" /></head><body>",$label_error);
			echo sprintf("<p>%s</p>",$output_text_004);
			echo "</body></html>";
			exit();
		}

	}
//	exit();
} // end if logged in

?>