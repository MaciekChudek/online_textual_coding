
#read in the data
result_table = read.csv("results.csv")

#extra the names of the coders and passages
coders =  unique(result_table$Coded_by)
passages = unique(result_table$Passage)

#create matrices to store our reliability data
mutually_coded = matrix(0,nrow = length(coders), ncol=length(coders), dimnames=list(coders,coders))
same_codes = matrix(0,nrow = length(coders), ncol=length(coders), dimnames=list(coders,coders))

#create data frame to easily look up where the errors later

error_table  = data.frame(row.names=passages)

#figure out which columns are comment columns - we don't care if there's different data in these, select out just the data columns
column_names = colnames(result_table)[4:ncol(result_table)]
data_columns = column_names[substr(column_names, nchar(column_names)-7, nchar(column_names)) != "comments"]




for (passage in  passages)
{
	for (c1 in 1:(length(coders)-1))
	{
		coder1 = coders[c1]
		
		for (c2 in (c1+1):length(coders))
		{
			coder2 = coders[c2]
			
			if (coder1 != coder2)
			{
				#check whether both coders have coded the passage
				a = which(result_table$Passage == passage & result_table$Coded_by == coder1)
				b = which(result_table$Passage == passage & result_table$Coded_by == coder2)

				if  (length(a) > 1 || length(b) > 1) #the passage has been coded more than once by a single coder, this shouldn't happen - alert the user
				{
					stop(paste("Passage", passage, "coded more than once by a single coder."));
				}
				
				if (length(a) == 1 && length(b) == 1) #the pasage has been coded by both coders
				{
					mutually_coded[as.character(coder1),as.character(coder2)] = mutually_coded[as.character(coder1),as.character(coder2)]  +1 #increment the mutually coded table
					
					mis_codes = result_table[a,data_columns] != result_table[b,data_columns]; #check which columns don't have identical codes
					
					if (length(mis_codes) == 0) #if their coding for this passage is in complete agreement,
					{
						same_codes[as.character(coder1),as.character(coder2)] = same_codes[as.character(coder1),as.character(coder2)]  +1 #increment the same_codes coded table
						error_table[as.character(passage), paste(coder1, coder2, sep=":")] = 1; #put a zero in the error table
					}
					else
					{
						error_table[as.character(passage), paste(coder1, coder2, sep=":")] = 0; #put a 1 in the error table
					}
				}
				else #the passages haven't been coded by both coders
				{
					error_table[as.character(passage), paste(coder1, coder2, sep=":")] = -1;
				}
			}
		}
	}
}

