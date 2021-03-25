/**
 * Class used to iterate over excel columns. Current limit extends to 676 columns.
 */
class alphaIterator{
	constructor(){
		this. characters = ['A','B','C','D','E','F','G','H','I','J','K','L','M',
		'N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
		this.currentChar;
		this.charNum=-1;
		this.row = 1;
	}
    /**
     * gets the next cell in the sequence and moves the internal column pointer over to the next column
     * @return String next cell
     */
	nextChar(){
		if(this.charNum==-1) {
			this.charNum = 1;
			this.currentChar=""+this.characters[this.charNum-1];
		}else if(this.charNum%26==0 && this.charNum>0) {
			this.currentChar=""+this.characters[Math.floor(this.charNum/26)-1]+this.characters[this.charNum++%26];
		}else {
			if(this.currentChar.length<1) {
				this.currentChar=""+this.characters[this.charNum++%26];
			}else {
				this.currentChar = this.currentChar.substring(0,this.currentChar.length-1)+this.characters[this.charNum++%26];
			}
		}
		return this.currentChar+this.row;
	}
    /**
     * gets the current cell without incrementing the internal column counter
     * @return String current cell
     */
    currentChar(){
		return this.currentChar+this.row;
	}
    /**
     * Sets the internal row counter to an input value
     * @param {Integer} inputRow row to set internal row 
     */
	setRow(inputRow){
		this.row = inputRow;
	}
    /**
     * Resets the internal column counter and increments the row counter
     */
	nextRow(){
		this.charNum=-1;
		this.row++;
		this.currentChar=null;
	}
    /**
     * Resets the column and row counters back to the start of the spreadsheet
     */
	reset(){
		this.currentChar = null;
		this.charNum=-1;
		this.row = 1;
	}
}