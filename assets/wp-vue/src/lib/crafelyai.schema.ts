import { z } from 'zod';

export const ProductSchema = z.object({
  name: z.string({message: "Product name can't be empty."}).min(2, { message: "Name is too short" }),
  description: z.string().optional(),
  short_description: z.string().optional(),
  image: z.string().url({ message: "Invalid image URL format" }).optional(),
  categories: z.string().optional(),
});

// Define the request payload schema
export const ProductDescriptionGenerateSchema = z.object({
  prompt: z.string().min(1, { message: "Prompt cannot be empty" }),
  type: z.enum(['short', 'long']),
  product: ProductSchema,
});

// Export inferred types from the schemas
export type Product = z.infer<typeof ProductSchema>;
export type ProductDescriptionPayload = z.infer<typeof ProductDescriptionGenerateSchema>;

// Validator functions
export const validateProduct = (data: unknown): Product => {
  return ProductSchema.parse(data);
};

export const validateProductPayload = (data: unknown): ProductDescriptionPayload => {
  return ProductDescriptionGenerateSchema.parse(data);
};

// Safe parse versions that don't throw
export const safeValidateProduct = (data: unknown) => {
  return ProductSchema.safeParse(data);
};

export const safeValidateProductPayload = (data: unknown) => {
  return ProductDescriptionGenerateSchema.safeParse(data);
};