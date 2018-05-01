<form action="../form-action.php" method="get">
	<table>
		<tr>
			<td colspan="2">
				<h2>Sample Form</h2>
			</td>
		</tr>
		<tr>
			<td>
				<label for="fname">Name</label>
			</td>
			<td>
				<input type="text" name="fname">
			</td>
		</tr>
		<tr>
			<td>
				<label for="age">Age</label>
			</td>
			<td>
				<input type="radio" name="age" value="18-20"> 18-20<br>
				<input type="radio" name="age" value="21-25"> 21-25<br>
				<input type="radio" name="age" value="26-30"> 26-30<br>
				<input type="radio" name="age" value="31-35"> 31-35<br>
				<input type="radio" name="age" value="35-40"> 35-40<br>
				<input type="radio" name="age" value="40+"> 40+
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="submit" value="Submit" >
			</td>
		</tr>
	</table>
</form>
