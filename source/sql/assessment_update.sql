-- Caskey, Damon V.
-- 2020-06-05
--
-- Record results of training assesment. This is an 
-- attempt to clean up the utter mess caused from years
-- of built up grandfathering from both my predessor 
-- and myself. 


USE [ehs_training]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[assessment_update]
	
	-- Parameters
	@param_account					varchar(50) = NULL,
	@param_name_last				varchar(50)	= NULL,
	@param_name_first				varchar(50)	= NULL,
	@param_room						varchar(8)	= NULL,
	@param_status					tinyint		= NULL,
	@param_phone					varchar(16)	= NULL,
	@param_department				varchar(50)	= NULL,
	@param_supervisor_name_first	varchar(20)	= NULL,
	@param_supervisor_name_last		varchar(20)	= NULL,
	@param_class_type				int			= NULL,
	@param_class_trainer			int			= NULL
	-- old
	
AS

	DECLARE @participant_update_result table(id_key int);
	DECLARE @class_instance_update_result table(id_key int);
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

BEGIN
        
	-- First we need to update the training participant
	-- information. We'll search by account name, and
	-- if we find a match we update that record. Otherwise
	-- we create and populate a new account record.
	--
	-- When we are done, we'll output the ID of updated
	-- or created record into a tablevar for downstream use.

	MERGE INTO ehsinfo.dbo.tbl_class_participant
		USING 
			(SELECT @param_account AS Search_Col) AS SRC
		ON 
			ehsinfo.dbo.tbl_class_participant.account = SRC.Search_Col
		WHEN MATCHED THEN
			UPDATE SET
				name_l				= @param_name_last,
				name_f				= @param_name_first,									
				room				= @param_room,
				status				= @param_status,
				phone				= @param_phone,									
				department			= @param_department,
				supervisor_name_f	= @param_supervisor_name_first,
				supervisor_name_l	= @param_supervisor_name_last
		WHEN NOT MATCHED THEN
			INSERT (account, name_l, name_f, room, status, phone, department, supervisor_name_f, supervisor_name_l)
			VALUES (SRC.Search_Col, @param_name_last, @param_name_first, @param_room, @param_status, @param_phone, @param_department, @param_supervisor_name_first, @param_supervisor_name_last)
			OUTPUT INSERTED.id INTO @participant_update_result;

	-- Insert a new record in the class instance
	-- table. Output its ID into a tablevar for
	-- downstream use.

	INSERT INTO	ehsinfo.dbo.tbl_class		
							(class_type,
							trainer_id)
							-- class_date (handled by default value in table field)
				OUTPUT INSERTED.class_id INTO @class_instance_update_result
							VALUES	(@param_class_type, @param_class_trainer)

	-- Now we need to tie participant to class 
	-- instance. Populate the listing table
	-- with participant ID and class instance
	-- ID from upstream queries.

	INSERT INTO ehsinfo.dbo.tbl_class_listing		
								(participant_id,
								class_id)
					OUTPUT INSERTED.id AS id_key
								VALUES ((SELECT id_key FROM @participant_update_result), (SELECT id_key FROM @class_instance_update_result))

END
