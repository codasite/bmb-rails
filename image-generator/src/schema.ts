import Joi from 'joi'

const queryParms = Joi.object({
  theme: Joi.string().required(),
  position: Joi.string().required(),
  title: Joi.string().required(),
  date: Joi.string().optional(),
  num_teams: Joi.number().integer().required(),
  inch_height: Joi.number().integer().optional(),
  inch_width: Joi.number().integer().optional(),
  picks: Joi.alternatives().try(Joi.string(), Joi.array()).required(),
  matches: Joi.alternatives().try(Joi.string(), Joi.array()).required(),
})

const S3StorageOptionsSchema = Joi.object({
  bucket: Joi.string().required(),
  key: Joi.string().required(),
})

export const generatorImageSchema = Joi.object({
  url: Joi.string().uri().optional(),
  queryParams: queryParms.required(),
  storageService: Joi.string().valid('s3').required(),
  storageOptions: S3StorageOptionsSchema.required(),
  pdf: Joi.boolean().optional(),
  deviceScaleFactor: Joi.number().optional(),
  inchHeight: Joi.number().integer().optional(),
  inchWidth: Joi.number().integer().optional(),
})
