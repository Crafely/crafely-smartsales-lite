import {
    Product,
    ProductDescriptionPayload,
} from '@/lib/crafelyai.schema'

export const getDescriptionGeneratePayload = ({
        type, 
        form
    }:{
        type: string,
         form: any
    }) => {
    const product: Product = form.values
    const payload = {
        type,
        prompt: `You are a helpful assistant for helping user to generate e-commerce product ${type} description. The description should be proper HTML formatted key highlighted with bold. Do not use any code block. pre and code tags.
        
        Generate ${type} description. Based on this product,  Name: ${product.name}, Current description: ${product.description}, Current short description: ${product.short_description}`,
        product: {
            name: product.name,
            description: product.description,
            short_description: product.short_description,
            image: product.image,
            categories: product.categories,
        }  
    }
    
    return payload as ProductDescriptionPayload
}