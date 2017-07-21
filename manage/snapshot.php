<?php
	require_once(__DIR__ . '/../php/classes/Config.class.php');
	require_once(__DIR__ . '/../php/classes/Element.class.php');
	require_once(__DIR__ . '/../php/classes/User.class.php');
	
	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER | TEACHER_ADMIN, 'manage');
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$documentroot = Config::get('documentroot');
	
	$teacherDomain = $license['DomainSuffix']; 
	$teacherID = $user->getColumn('Id');
	$groupName = $user->getColumn('Groups');
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head('Fluency Games User Portal'); ?>
	
	<script src="<?php echo $documentroot; ?>js/math.js"></script>
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>
	
	<script type="text/javascript">
		var defaultScoreDivClass = 'score-circle';
		var snapshots = [];
		
		function createScorePair(score, div, type) {
			return {
				score: score, // TODO(bret): Make this per game
				div: div,
				type: type,
			};
		}
		
		Snapshot = function(data) {
			var $this = this;
			
			this.scoreInfo = [
				createScorePair(0, null, 'Average'),
				createScorePair(0, null, 'Points/Second'),
				createScorePair(0, null, 'Accuracy'),
				
				createScorePair(0, null, 'Average'),
				createScorePair(0, null, 'Points/Second'),
				createScorePair(0, null, 'Accuracy'),
			];
			
			parentDiv = $('<div class="snapshot" data-id="' + data.id + '"></div>');
			
			parentRow = $('<div class="row"></div>');
			parentDiv.append(parentRow);
			
			nameCol = $('<div class="col-xs-3"><span class="name">' + data.name + '</span></div>');
			parentRow.append(nameCol);
			
			/*<input id="username-' + data.username + '" type="hidden" name="username" value="' + data.username + '" />*/
			
			for (var colIndex = 0; colIndex < 2; ++colIndex) {
				scoreCol = $('<div class="col-xs-4"></div>');
				parentRow.append(scoreCol);
				
				scorePair = $('<div class="scores-3-pair"></div>');
				scoreCol.append(scorePair);
				
				scorePairRow = $('<div class="row"></div>');
				scorePair.append(scorePairRow);
				
				for (var scoreIndex = 0; scoreIndex < 3; ++scoreIndex) {
					var col = $('<div class="col-xs-4"></div>');
					var scoreDiv = $('<div class="' + defaultScoreDivClass + '" data-toggle="tooltip" title="XX%"></div>');
					col.append(scoreDiv);
					scoreDiv.tipsy({ gravity: 's', html: true });
					$this.scoreInfo[(colIndex * 3) + scoreIndex].div = scoreDiv;
					scorePairRow.append(col);
				}
			}
			
			scores = [
				Math.random(),
				Math.random(),
				Math.random(),
				Math.random(),
				Math.random(),
				Math.random()
			];
			this.updateScores(scores);
			
			this.div = parentDiv;
			$("#snapshots").append(parentDiv);
		}
		
		Snapshot.prototype.updateScores = function(scores) {
			for (var s = 0; s < 6; ++s) {
				this.scoreInfo[s].score = scores[s];
				this.scoreInfo[s].div.attr('class', defaultScoreDivClass);
				
				var percent = (scores[s] * 100).toFixed(2);
				var type = this.scoreInfo[s].type + ((s < 3) ? ' (Summary)' : ' (Last Game)');
				this.scoreInfo[s].div.attr('title', type + '<br />' + percent + '%');
				
				if (scores[s] >= 0.7)
					this.scoreInfo[s].div.addClass('green');
				else if (scores[s] < 0.5)
					this.scoreInfo[s].div.addClass('red');
			}
		}
		
		$(window).ready(function() {
			snapshots[1] = new Snapshot({ id: 1, name: 'George' });
			snapshots[2] = new Snapshot({ id: 2, name: 'Anthony' });
		});
	</script>
	<!-- <script src="<?php echo $documentroot; ?>js/manage/users.js"></script> -->
</head>
<body>
	<?php Element::header(5); ?>
	<div class="body">
		<div class="container">
			
			<div class="row">
				<?php Element::sidebarManage(6); ?>
				<div class="col-xs-12 col-sm-8 col-lg-9">
					
					<div class="card">
						<div class="head center">
							<?php echo $user->getFullname() . ' (Group: ' . $user->getColumn('Groups') . ')'; ?>
						</div>
						
						<div class="snapshot product" data-id="0">
							<span class="name">Select Product:</span>
							<input type="hidden" name="username" value="<?php echo '*.' . $groupName . '.' . $teacherDomain; ?>" />
							<select id="product" class="right-aligned-products">
								<?php if($products & 0x01) { ?> <option value="1" >Addition Blocks</option> <?php } ?>
								<?php if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option> <?php } ?>
								<?php if($products & 0x04) { ?> <option value="4" >Percent Bingo</option> <?php } ?>
								<?php if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option> <?php } ?>
								<?php if($products & 0x10) { ?> <option value="16">Integer Blocks</option> <?php } ?>
                                <option value="128">Facts Assessment</option>
							</select>
						</div>
						<!--<div class="snapshot">
							<span class="name">Name (Games)</span>
							<?php
								for ($i = 0; $i < 2; ++$i) {
							?>
							<div class="scores-3-pair">
								<div class="score-title">Avg</div>
								<div class="score-title">Pts/<br/>Sec</div>
								<div class="score-title">Accu.</div>
							</div>
							<?php } ?>
						</div>-->
						<div class="snapshot">
							<div class="row">
								<div class="col-xs-3">
									<span class="name" style="height: 50px !important; line-height: 60px;">Name (Games)</span>
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Summary
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Last Game
								</div>
									<?php
										for ($i = 0; $i < 2; ++$i) {
									?>
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<div class="col-xs-4">
												<div class="score-title" data-toggle="tooltip"title="Average">Avg</div>
											</div>
											<div class="col-xs-4">
												<div class="score-title" data-toggle="tooltip" title="Points Per Second">PPS</div>
											</div>
											<div class="col-xs-4">
												<div class="score-title" data-toggle="tooltip" title="Accuracy">Acc</div>
											</div>
										</div>
									</div>
								</div>
									<?php } ?>
							</div>
						</div>
						<div id="snapshots">
							<?php
								/*function createStudent($lname, $fname, $username, $id) {
									$name = $lname . ', ' . $fname;
							?>
							
							<?php
								}
								
								$students = $user->getStudents('ORDER BY Lname ASC, Fname ASC', '*', $groupName);
								foreach ($students as &$student) {
									$student['Lname'] = User::decode($student['Lname']);
									$student['Fname'] = User::decode($student['Fname']);
								}
								$user->sortStudents($students);
								foreach ($students as $s) {
									createStudent($s['Lname'], $s['Fname'], $s['Username'], $s['Id']);
								}*/
							?>
						</div>
						<div class="footer center">
							End of students
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>