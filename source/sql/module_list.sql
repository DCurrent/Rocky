USE [ehs_training]
GO
/****** Object:  StoredProcedure [dbo].[module_list]    Script Date: 11/30/2020 2:01:08 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get list of tickets, ordered and paged.
-- =============================================

ALTER PROCEDURE [dbo].[module_list]
	
	-- Parameters
	
	-- paging
	@page_current		int				= 1,
	@page_rows			int				= 10,	
	@page_last			float			OUTPUT,
	@row_count_total	int				OUTPUT	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters		
		
		-- Current page.
		IF		@page_current IS NULL SET @page_current = 1
		ELSE IF @page_current < 1 SET @page_current = 1

		-- Rows per page maximum.
		IF		@page_rows IS NULL SET @page_rows = 10
		ELSE IF @page_rows < 1 SET @page_rows = 10

	-- Determine the first record and last record 
	DECLARE @row_first int, 
			@row_last int
	
	-- Set up table var so we can reuse results.		
	DECLARE @tempMain TABLE
	(
		row int,
		id int, 
		desc_title varchar(75),
		intro varchar(max),
		log_create datetime2,
		log_update datetime2
	)	
	
	-- Populate paging first and last row limits.
	SELECT @row_first = (@page_current - 1) * @page_rows
	SELECT @row_last = (@page_current * @page_rows + 1);	
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO @tempMain (row, id, desc_title, intro, log_create, log_update)
	(SELECT ROW_NUMBER() OVER(ORDER BY 
								desc_title) 
		AS _row_number,
			_main.id, 
			_main.desc_title, 
			
			-- Limit output length of intro.
			CASE 
				WHEN len(_main.intro)> 45
				THEN left(_main.intro, 45) + '...' 
				ELSE _main.intro END intro,

			_main.log_create,
			_main.log_update
	FROM ehsinfo.dbo.tbl_class_train_parameters _main
	WHERE (record_deleted IS NULL OR record_deleted = 0))	
	
	-- Extract paged rows from main tabel var.
	SELECT TOP (@row_last-1) *
	FROM @tempMain	 
	WHERE row > @row_first 
		AND row < @row_last
	ORDER BY row
	
	-- Get a count of records without paging. We'll need this for control
	-- code and for calculating last page. 
	SELECT @row_count_total = (SELECT COUNT(id) FROM @tempMain);
	
	-- Get last page. This is for use by control code.
	SELECT @page_last = (SELECT CEILING(CAST(@row_count_total AS FLOAT) / CAST(@page_rows AS FLOAT)))
	IF @page_last = 0 SET @page_last = 1
	
	
